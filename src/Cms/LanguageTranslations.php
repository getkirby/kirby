<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Filesystem\F;

/**
 * With helper methods provides get language translations or
 * loads from custom `translations` root
 * @since 4.2.0
 *
 * @package   Kirby Cms
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguageTranslations
{
	protected array $data;

	public function __construct(
		protected Language $language,
		self|array $translations = []
	) {
		$this->setTranslations($translations);
	}

	/**
	 * Returns a single translation string by key
	 */
	public function get(string $key, string $default = null): string|null
	{
		return $this->data[$key] ?? $default;
	}

	/**
	 * Loads the language translations based on custom roots for provided language code
	 */
	public function load(array $default = []): array
	{
		if ($file = $this->root()) {
			try {
				return Data::read($file);
			} catch (Exception) {
				return $default;
			}
		}

		return $default;
	}

	/**
	 * Saves the language translations in the custom root
	 * @internal
	 *
	 * @return $this
	 */
	public function save(array $translations = []): static
	{
		$this->setTranslations($translations);

		if ($root = $this->root()) {
			Data::write($root, $translations);
		}

		return $this;
	}

	/**
	 * Returns custom translations root path if defined
	 */
	public function root(): string|null
	{
		$kirby = App::instance();
		$root  = $kirby->root('translations');
		$file  = ($root ?? '') . '/' . $this->language->code() . '.php';

		if (
			$root !== null &&
			F::exists($file) === true
		) {
			return $file;
		}

		return null;
	}

	/**
	 * Removes a translation key
	 *
	 * @return $this
	 */
	public function remove(string $key): static
	{
		unset($this->data[$key]);
		return $this;
	}

	/**
	 * Sets the translation key
	 *
	 * @return $this
	 */
	public function set(string $key, string|null $value = null): static
	{
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Set translations
	 *
	 * @return $this
	 */
	public function setTranslations(self|array $translations = []): static
	{
		$this->data = match (true) {
			empty($translations) === true => $this->load(),
			$translations instanceof self => $translations->toArray(),
			default                       => $translations
		};

		return $this;
	}

	/**
	 * Returns translations
	 */
	public function toArray(): array
	{
		return $this->data;
	}
}
