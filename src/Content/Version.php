<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;

/**
 * The Version class handles all actions for a single
 * version and is identified by a VersionId instance
 *
 * @internal
 * @since 5.0.0
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Version
{
	public function __construct(
		protected ModelWithContent $model,
		protected VersionId $id
	) {
	}

	/**
	 * Returns a Content object for the given language
	 */
	public function content(string $language = 'default'): Content
	{
		return new Content(
			parent: $this->model,
			data:   $this->model->storage()->read($this->id, $this->language($language)),
		);
	}

	/**
	 * Creates a new version for the given language
     *
	 * @param array<string, string> $fields Content fields
	 */
	public function create(array $fields, string $language = 'default'): void
	{
		$this->model->storage()->create($this->id, $this->language($language), $fields);
	}

	/**
	 * Deletes a version by language or for any language
	 *
	 * @param string|null $language If null, all available languages will be deleted
	 */
	public function delete(string|null $language = null): void
	{
		// delete a single language
		if ($this->model->kirby()->multilang() === false) {
			$this->deleteLanguage('default');
		}

		// delete a specific language
		if ($language !== null) {
			$this->deleteLanguage($language);
			return;
		}

		// delete all languages
		foreach ($this->model->kirby()->languages() as $language) {
			$this->deleteLanguage($language);
		}
	}

	/**
	 * Deletes a version by a specific language
	 */
	public function deleteLanguage(string $language = 'default'): void
	{
		$this->model->storage()->delete($this->id, $this->language($language));
	}

	/**
	 * Ensure that the version exists and otherwise
	 * throw an exception
	 *
	 * @throws \Kirby\Exception\NotFoundException if the version does not exist
	 */
	public function ensure(
		string $language = 'default'
	): bool {
		if ($this->exists($language) !== true) {
			$message = match($this->model->kirby()->multilang()) {
				true  => 'Version "' . $this->id . ' (' . $language . ')" does not already exist',
				false => 'Version "' . $this->id . '" does not already exist',
			};

			throw new NotFoundException($message);
		}

		return true;
	}

	/**
	 * Checks if a version exists for the given language
	 */
	public function exists(string $language = 'default'): bool
	{
		return $this->model->storage()->exists($this->id, $this->language($language));
	}

	/**
	 * Returns the VersionId instance for this version
	 */
	public function id(): VersionId
	{
		return $this->id;
	}

	/**
	 * Converts a "user-facing" language code to a `Language` object
     * to be used in storage methods
	 */
	protected function language(
		string|null $languageCode = null,
	): Language {
		// single language
		if ($this->model->kirby()->multilang() === false) {
			return Language::single();
		}

		// look up the actual language object if possible
		if ($language = $this->model->kirby()->language($languageCode)) {
			return $language;
		}

		// validate the language code
		throw new InvalidArgumentException('Invalid language: ' . $languageCode);
	}

	/**
	 * Returns the parent model
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Returns the modification timestamp of a version
	 * if it exists
	 */
	public function modified(
		string $language = 'default'
	): int|null {
		$this->ensure($language);
		return $this->model->storage()->modified($this->id, $this->language($language));
	}

	/**
	 * Moves the version to a new language and/or version
	 */
	public function move(string $fromLanguage, VersionId $toVersionId, string $toLanguage): void
	{
		$this->ensure($fromLanguage);
		$this->model->storage()->move(
			fromVersionId: $this->id,
			fromLanguage: $this->language($fromLanguage),
			toVersionId: $toVersionId,
			toLanguage: $this->language($toLanguage)
		);
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 */
	public function read(string $language = 'default'): array
	{
		$this->ensure($language);
		return $this->model->storage()->read($this->id, $this->language($language));
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @param string|null $language If null, all available languages will be touched
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(string|null $language = null): void
	{
		// touch a single language
		if ($this->model->kirby()->multilang() === false) {
			$this->touchLanguage('default');
			return;
		}

		// touch a specific language
		if ($language !== null) {
			$this->touchLanguage($language);
			return;
		}

		// touch all languages
		foreach ($this->model->kirby()->languages() as $language) {
			$this->touchLanguage($language);
		}
	}

	/**
	 * Updates the modification timestamp of a specific language
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touchLanguage(string $language = 'default'): void
	{
		// make sure the version exists
		$this->ensure($language);
		$this->model->storage()->touch($this->id, $this->language($language));
	}

	/**
	 * Updates the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function update(array $fields, string $language = 'default'): void
	{
		$this->ensure($language);
		$this->model->storage()->update($this->id, $this->language($language), $fields);
	}
}
