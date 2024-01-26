<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Filesystem\F;
use Throwable;

/**
 * Manages the variables string for a language,
 * either from the language file or `language:variables` root
 * @since 5.0.0
 *
 * @package   Kirby Cms
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguageVariables
{
	public function __construct(
		protected Language $language,
		protected array $data = []
	) {
		$this->data = [...$this->load(), ...$this->data];
	}

	/**
	 * Deletes the current language variables file
	 * if custom root defined
	 */
	public function delete(): void
	{
		if ($file = $this->root()) {
			if (F::remove($file) !== true) {
				throw new Exception('The language variables could not be deleted');
			}
		}
	}

	/**
	 * Returns a single variable string by key
	 */
	public function get(string $key, string|null $default = null): string|null
	{
		return $this->data[$key] ?? $default;
	}

	/**
	 * Loads the language variables based on custom roots
	 */
	public function load(): array
	{
		if ($file = static::root()) {
			try {
				return Data::read($file);
			} catch (Throwable) {
				// skip when an exception thrown
			}
		}

		return [];
	}

	/**
	 * Saves the language variables in the custom root
	 * @return $this
	 * @internal
	 */
	public function save(array $variables = []): static
	{
		$this->data = $variables;

		if ($root = $this->root()) {
			Data::write($root, $this->data);
		}

		return $this;
	}

	/**
	 * Returns custom variables root path if defined
	 */
	public function root(): string|null
	{
		if ($root = App::instance()->root('language:variables')) {
			return $root . '/' . $this->language->code() . '.php';
		}

		return null;
	}

	/**
	 * Removes a variable key
	 *
	 * @return $this
	 */
	public function remove(string $key): static
	{
		unset($this->data[$key]);
		return $this;
	}

	/**
	 * Sets the variable key
	 *
	 * @return $this
	 */
	public function set(string $key, string|null $value = null): static
	{
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Returns variables
	 */
	public function toArray(): array
	{
		return $this->data;
	}

	/**
	 * Updates the variables data
	 */
	public function update(array $data = []): static
	{
		$this->data = $data;
		return $this;
	}
}
