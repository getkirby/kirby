<?php

namespace Kirby\Content;

use Kirby\Cms\Language;

/**
 * Version Memory can be used to store temporary
 * values for models that are used in templates or
 * controllers, but are never stored.
 *
 * @since 5.0.0
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class VersionMemory
{
	public function __construct(
		protected Version $version,
		protected Language $language
	) {
	}

	/**
	 * Reads all values from memory
	 */
	public function read(): array
	{
		$fields = VersionMemoryCache::get($this->version, $this->language) ?? [];
		return array_filter($fields, fn ($value) => $value !== null);
	}

	/**
	 * Removes all fields from memory
	 */
	public function flush(): static
	{
		VersionMemoryCache::remove($this->version, $this->language);
		return $this;
	}

	/**
	 * Returns a single value from memory if it exists
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->read()[$key] ?? $default;
	}

	/**
	 * Removes a single field from memory
	 */
	public function remove(string $key): static
	{
		$memory = $this->read();

		unset($memory[$key]);

		return $this->write($memory);
	}

	/**
	 * Sets a single value in memory
	 */
	public function set(string $key, mixed $value): static
	{
		return $this->update([
			$key => $value
		]);
	}

	/**
	 * Updates multiple values in memory
	 */
	public function update(array $fields): static
	{
		return $this->write([
			...$this->read(),
			...$fields
		]);
	}

	/**
	 * Writes the given fields to memory
	 */
	public function write(array $fields): static
	{
		$fields = array_change_key_case($fields, CASE_LOWER);
		VersionMemoryCache::set($this->version, $this->language, $fields);
		return $this;
	}
}
