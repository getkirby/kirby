<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Filesystem\F;
use Throwable;

/**
 * Manages the translations string for a language,
 * either from the language file or `translations` root
 * @since 5.0.0
 *
 * @package   Kirby Cms
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguageTranslations
{
	public function __construct(
		protected Language $language,
		protected array $data = []
	) {
		$this->data = [...$this->load(), ...$this->data];
	}

	/**
	 * Deletes the current language translations file
	 * if custom root defined
	 */
	public function delete(): void
	{
		if ($file = $this->root()) {
			if (F::remove($file) !== true) {
				throw new Exception('The language translations could not be deleted');
			}
		}
	}

	/**
	 * Returns a single translation string by key
	 */
	public function get(string $key, string|null $default = null): string|null
	{
		return $this->data[$key] ?? $default;
	}

	/**
	 * Loads the language translations based on custom roots
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
	 * Saves the language translations in the custom root
	 * @return $this
	 * @internal
	 */
	public function save(array $translations = []): static
	{
		$this->data = $translations;

		if ($root = $this->root()) {
			Data::write($root, $this->data);
		}

		return $this;
	}

	/**
	 * Returns custom translations root path if defined
	 */
	public function root(): string|null
	{
		if ($root = App::instance()->root('translations')) {
			return $root . '/' . $this->language->code() . '.php';
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
	 * Returns translations
	 */
	public function toArray(): array
	{
		return $this->data;
	}

	/**
	 * Updates the translations data
	 */
	public function update(array $data = []): static
	{
		$this->data = $data;
		return $this;
	}
}
