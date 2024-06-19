<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;
use Throwable;

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
	public function content(Language|string $language = 'default'): Content
	{
		return new Content(
			parent: $this->model,
			data:   $this->read($language),
		);
	}

	/**
	 * Provides simplified access to the absolute content file path.
	 * This should stay an internal method and be removed as soon as
	 * the dependency on file storage methods is resolved more clearly.
	 *
	 * @internal
	 */
	public function contentFile(Language|string $language = 'default'): string
	{
		return $this->model->storage()->contentFile($this->id, Language::ensure($language));
	}

	/**
	 * Make sure that all field names are converted to lower
	 * case to be able to merge and filter them properly
	 */
	protected function convertFieldNamesToLowerCase(array $fields): array
	{
		return array_change_key_case($fields, CASE_LOWER);
	}

	/**
	 * Creates a new version for the given language
	 * @todo Convert to a static method that creates the version initially with all relevant languages
	 *
	 * @param array<string, string> $fields Content fields
	 */
	public function create(array $fields, Language|string $language = 'default'): void
	{
		$language = Language::ensure($language);

		$fields = $this->convertFieldNamesToLowerCase($fields);
		$fields = $this->removeUnwantedFields($fields, $language);

		$this->model->storage()->create($this->id, $language, $fields);
	}

	/**
	 * Deletes a version with all its languages
	 */
	public function delete(): void
	{
		foreach (Languages::ensure() as $language) {
			$this->model->storage()->delete($this->id, $language);
		}
	}

	/**
	 * Ensure that the version exists and otherwise
	 * throw an exception
	 *
	 * @throws \Kirby\Exception\NotFoundException if the version does not exist
	 */
	public function ensure(
		Language|string $language = 'default'
	): void {
		$this->model->storage()->ensure($this->id, Language::ensure($language));
	}

	/**
	 * Checks if a version exists for the given language
	 */
	public function exists(Language|string $language = 'default'): bool
	{
		return $this->model->storage()->exists($this->id, Language::ensure($language));
	}

	/**
	 * Returns the VersionId instance for this version
	 */
	public function id(): VersionId
	{
		return $this->id;
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
		Language|string $language = 'default'
	): int|null {
		if ($this->exists($language) === true) {
			return $this->model->storage()->modified($this->id, Language::ensure($language));
		}

		return null;
	}

	/**
	 * Moves the version to a new language and/or version
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function move(
		Language|string $fromLanguage,
		VersionId $toVersionId,
		Language|string $toLanguage
	): void {
		$this->ensure($fromLanguage);
		$this->model->storage()->move(
			fromVersionId: $this->id,
			fromLanguage: Language::ensure($fromLanguage),
			toVersionId: $toVersionId,
			toLanguage: Language::ensure($toLanguage)
		);
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 */
	public function read(Language|string $language = 'default'): array
	{
		$language = Language::ensure($language);

		try {
			$fields = $this->model->storage()->read($this->id, $language);
			$fields = $this->convertFieldNamesToLowerCase($fields);
			return $fields;
		} catch (Throwable) {
			return [];
		}
	}

	/**
	 * Remove fields that should not be stored for the given version and language
	 */
	protected function removeUnwantedFields(array $fields, Language $language): array
	{
		// the default language stores all fields
		if ($language->isDefault() === true) {
			return $fields;
		}

		// remove all untranslatable fields
		foreach ($this->model->blueprint()->fields() as $field) {
			if (($field['translate'] ?? true) === false) {
				unset($fields[strtolower($field['name'])]);
			}
		}

		// remove UUID for non-default languages
		unset($fields['uuid']);

		return $fields;
	}

	/**
	 * Replaces the content of the current version with the given fields
	 *
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function replace(array $fields, Language|string $language = 'default'): void
	{
		$this->ensure($language);

		$language = Language::ensure($language);

		$fields = $this->convertFieldNamesToLowerCase($fields);
		$fields = $this->removeUnwantedFields($fields, $language);

		$this->model->storage()->update($this->id, $language, $fields);
	}

	/**
	 * Convenience wrapper around ::create, ::replace and ::update.
	 */
	public function save(
		array $fields,
		Language|string $language = 'default',
		bool $overwrite = false
	): void {
		match (true) {
			$this->exists($language) === false
				=> $this->create($fields, $language),
			$overwrite === true
				=> $this->replace($fields, $language),
			default
			=> $this->update($fields, $language)
		};
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(Language|string $language = 'default'): void
	{
		$this->ensure($language);
		$this->model->storage()->touch($this->id, Language::ensure($language));
	}

	/**
	 * Updates the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function update(array $fields, Language|string $language = 'default'): void
	{
		$this->ensure($language);

		$language = Language::ensure($language);

		// merge the previous state with the new state to always
		// update to a complete version
		$fields = [
			...$this->read($language),
			...$this->convertFieldNamesToLowerCase($fields)
		];

		// make sure to not store unnecessary fields for the version & language
		$fields = $this->removeUnwantedFields($fields, $language);

		$this->model->storage()->update($this->id, $language, $fields);
	}
}
