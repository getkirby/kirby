<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;

/**
 * A language variable is a custom translation string
 * Those are stored in /site/languages/$code.php in the
 * translations array
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguageVariable
{
	protected App $kirby;

	public function __construct(
		protected Language $language,
		protected string $key
	) {
		$this->kirby = App::instance();
	}

	/**
	 * Creates a new language variable. This will
	 * be added to the default language first and
	 * can then be translated in other languages.
	 */
	public static function create(
		string $key,
		string|null $value = null
	): static {
		if (is_numeric($key) === true) {
			throw new InvalidArgumentException(
				message: 'The variable key must not be numeric'
			);
		}

		if (empty($key) === true) {
			throw new InvalidArgumentException(
				message: 'The variable needs a valid key'
			);
		}

		$kirby     = App::instance();
		$language  = $kirby->defaultLanguage();
		$variables = $language->variables()->toArray();

		if ($kirby->translation()->get($key) !== null) {
			if (isset($variables[$key]) === true) {
				throw new DuplicateException(
					message: 'The variable already exists'
				);
			}

			throw new DuplicateException(
				message: 'The variable is part of the core translation and cannot be overwritten'
			);
		}

		$variables[$key] = $value ?? '';

		$language->update(['variables' => $variables]);

		return $language->variable($key);
	}

	/**
	 * Deletes a language variable from the translations array.
	 * This will go through all language files and delete the
	 * key from all translation arrays to keep them clean.
	 */
	public function delete(): bool
	{
		// go through all languages and remove the variable
		foreach ($this->kirby->languages() as $language) {
			$variables = $language->variables();
			$variables->remove($this->key);
			$language->update(['variables' => $variables->toArray()]);
		}

		return true;
	}

	/**
	 * Checks if a language variable exists in the default language
	 */
	public function exists(): bool
	{
		$language = $this->kirby->defaultLanguage();
		return $language->variables()->get($this->key) !== null;
	}

	/**
	 * Returns the unique key for the variable
	 */
	public function key(): string
	{
		return $this->key;
	}

	/**
	 * Sets a new value for the language variable
	 */
	public function update(string|null $value = null): static
	{
		$variables = $this->language->variables();
		$variables->set($this->key, $value);

		$language = $this->language->update(['variables' => $variables->toArray()]);

		return $language->variable($this->key);
	}

	/**
	 * Returns the value if the variable has been translated
	 */
	public function value(): string|null
	{
		return $this->language->variables()->get($this->key);
	}
}
