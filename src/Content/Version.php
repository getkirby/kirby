<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\LogicException;
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
	public function content(Language|string $language = 'default'): Content
	{
		$language = Language::ensure($language);
		$fields   = $this->read($language) ?? [];

		// This is where we merge content from the default language
		// to provide a fallback for missing/untranslated fields.
		//
		// @todo This is the critical point that needs to be removed/refactored
		// in the future, to provide multi-language support with truly
		// individual versions of pages and no longer enforce the fallback.
		if ($language->isDefault() === false) {
			$fields = [
				...$this->read('default') ?? [],
				...$fields
			];
		}

		return new Content(
			parent: $this->model,
			data:   $fields,
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

		$this->model->storage()->create(
			versionId: $this->id,
			language: $language,
			fields: $this->prepareFieldsBeforeWrite($fields, $language)
		);
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
	 * Returns the changed fields, compared to the given version
	 */
	public function diff(VersionId|string $versionId, Language|string $language = 'default'): array
	{
		$versionId = VersionId::from($versionId);

		if ($versionId->is($this->id) === true) {
			return [];
		}

		$a = $this->read($language) ?? [];
		$b = $this->model->version($versionId)->read($language) ?? [];

		return array_diff($b, $a);
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
	 * Prepare fields to be written by removing unwanted fields
	 * depending on the language or model and by cleaning the field names
	 */
	protected function prepareFieldsBeforeWrite(array $fields, Language $language): array
	{
		// convert all field names to lower case
		$fields = $this->convertFieldNamesToLowerCase($fields);

		// make sure to store the right fields for the model
		$fields = $this->model->contentFileData($fields, $language);

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
	 * Make sure that reading from storage will always
	 * return a usable set of fields with clean field names
	 */
	protected function prepareFieldsAfterRead(array $fields, Language $language): array
	{
		return $this->convertFieldNamesToLowerCase($fields);
	}

	/**
	 * This method can only be applied to the "changes" version.
	 * It will copy all fields over to the "published" version and delete
	 * this version afterwards.
	 */
	public function publish(Language|string $language = 'default'): void
	{
		if ($this->id->value() === VersionId::PUBLISHED) {
			throw new LogicException(
				message: 'This version is already published'
			);
		}

		$language = Language::ensure($language);

		// the version needs to exist
		$this->ensure($language);

		// update the published version
		$this->model->version(VersionId::published())->save(
			fields: $this->read($language),
			language: $language
		);

		// delete the changes
		$this->delete($language);
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>|null
	 */
	public function read(Language|string $language = 'default'): array|null
	{
		$language = Language::ensure($language);

		try {
			$fields = $this->model->storage()->read($this->id, $language);
			$fields = $this->prepareFieldsAfterRead($fields, $language);
			return $fields;
		} catch (NotFoundException) {
			return null;
		}
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

		$this->model->storage()->update(
			versionId: $this->id,
			language: $language,
			fields: $this->prepareFieldsBeforeWrite($fields, $language)
		);
	}

	/**
	 * Convenience wrapper around ::create, ::replace and ::update.
	 */
	public function save(
		array $fields,
		Language|string $language = 'default',
		bool $overwrite = false
	): void {
		if ($this->exists($language) === false) {
			$this->create($fields, $language);
			return;
		}

		if ($overwrite === true) {
			$this->replace($fields, $language);
			return;
		}

		$this->update($fields, $language);
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
			...$fields
		];

		$this->model->storage()->update(
			versionId: $this->id,
			language: $language,
			fields: $this->prepareFieldsBeforeWrite($fields, $language)
		);
	}
}
