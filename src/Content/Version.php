<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Reform;
use Kirby\Http\Uri;

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

		// prepare raw content file fields as fields for Content object
		$fields = $this->prepareFieldsForContent($fields, $language);

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
		return $this->model->storage()->contentFile(
			$this->id,
			Language::ensure($language)
		);
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
	public function create(
		array $fields,
		Language|string $language = 'default'
	): void {
		$language = Language::ensure($language);
		$latest   = $this->model->version(VersionId::latest());

		// if the latest version of the translation does not exist yet,
		// we have to copy over the content from the default language first.
		if (
			$this->isLatest() === false &&
			$language->isDefault() === false &&
			$latest->exists($language) === false
		) {
			$latest->create(
				fields: $latest->read(Language::ensure('default')),
				language: $language
			);
		}

		// check if creating is allowed
		VersionRules::create($this, $fields, $language);

		// track the changes
		if ($this->id->is(VersionId::changes()) === true) {
			(new Changes())->track($this->model);
		}

		$this->model->storage()->create(
			versionId: $this->id,
			language: $language,
			fields: $this->prepareFieldsBeforeWrite($fields, $language)
		);
	}

	/**
	 * Deletes a version for a specific language
	 */
	public function delete(Language|string $language = 'default'): void
	{
		$language = Language::ensure($language);

		// check if deleting is allowed
		VersionRules::delete($this, $language);

		$this->model->storage()->delete($this->id, $language);

		// untrack the changes if the version does no longer exist
		// in any of the available languages
		if ($this->id->is(VersionId::changes()) === true && $this->exists('*') === false) {
			(new Changes())->untrack($this->model);
		}
	}

	/**
	 * Checks if a version exists for the given language
	 */
	public function exists(Language|string $language = 'default'): bool
	{
		// go through all possible languages to check if this
		// version exists in any language
		if ($language === '*') {
			foreach (Languages::ensure() as $language) {
				if ($this->exists($language) === true) {
					return true;
				}
			}

			return false;
		}

		return $this->model->storage()->exists(
			$this->id,
			Language::ensure($language)
		);
	}

	/**
	 * Returns the VersionId instance for this version
	 */
	public function id(): VersionId
	{
		return $this->id;
	}

	/**
	 * Returns whether the content of both versions
	 * is identical
	 */
	public function isIdentical(
		Version|VersionId|string $version,
		Language|string $language = 'default'
	): bool {
		if (is_string($version) === true) {
			$version = VersionId::from($version);
		}

		if ($version instanceof VersionId) {
			$version = $this->model->version($version);
		}

		if ($version->id()->is($this->id) === true) {
			return true;
		}

		$language = Language::ensure($language);

		// read fields low-level from storage
		$a = $this->read($language) ?? [];
		$b = $version->read($language) ?? [];

		// apply same preparation as for content
		$a = $this->prepareFieldsForContent($a, $language);
		$b = $this->prepareFieldsForContent($b, $language);

		// remove additional fields that should not be
		// considered in the comparison
		unset(
			$a['uuid'],
			$b['uuid']
		);

		$form = new Reform(
			model: $this->model,
			language: $language,
		);

		$a = $form->fill($a)->toFormValues();
		$b = $form->fill($b)->toFormValues();

		ksort($a);
		ksort($b);

		return $a === $b;
	}

	/**
	 * Checks if the version is the latest version
	 */
	public function isLatest(): bool
	{
		return $this->id->is('latest');
	}

	/**
	 * Checks if the version is locked for the current user
	 */
	public function isLocked(Language|string $language = 'default'): bool
	{
		return $this->lock($language)->isLocked();
	}

	/**
	 * Returns the lock object for the version
	 */
	public function lock(Language|string $language = 'default'): Lock
	{
		return Lock::for($this, $language);
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
			return $this->model->storage()->modified(
				$this->id,
				Language::ensure($language)
			);
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
		VersionId|null $toVersionId = null,
		Language|string|null $toLanguage = null,
		Storage|null $toStorage = null
	): void {
		$fromVersion  = $this;
		$fromLanguage = Language::ensure($fromLanguage);
		$toLanguage   = Language::ensure($toLanguage ?? $fromLanguage);
		$toVersion    = $this->model->version($toVersionId ?? $this->id);

		// check if moving is allowed
		VersionRules::move(
			fromVersion: $fromVersion,
			fromLanguage: $fromLanguage,
			toVersion: $toVersion,
			toLanguage: $toLanguage
		);

		$this->model->storage()->move(
			fromVersionId: $fromVersion->id(),
			fromLanguage: $fromLanguage,
			toVersionId: $toVersion->id(),
			toLanguage: $toLanguage,
			toStorage: $toStorage
		);
	}

	/**
	 * Prepare fields to be written by removing unwanted fields
	 * depending on the language or model and by cleaning the field names
	 */
	protected function prepareFieldsBeforeWrite(
		array $fields,
		Language $language
	): array {
		// convert all field names to lower case
		$fields = $this->convertFieldNamesToLowerCase($fields);

		// make sure to store the right fields for the model
		$fields = $this->model->contentFileData($fields, $language);

		// add the editing user
		if (
			Lock::isEnabled() === true &&
			$this->id->is(VersionId::changes()) === true
		) {
			$fields['lock'] = $this->model->kirby()->user()?->id();

		// remove the lock field for any other version or
		// if locking is disabled
		} else {
			unset($fields['lock']);
		}

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
	 * Make sure that the Content object receives the right set of fields
	 * filtering fields used for lower logic (e.g. lock)
	 */
	protected function prepareFieldsForContent(
		array $fields,
		Language $language
	): array {
		unset($fields['lock']);

		if ($this->model instanceof Page) {
			unset($fields['slug']);
		}

		return $fields;
	}

	/**
	 * Returns a verification token for the authentication
	 * of draft previews
	 * @internal
	 */
	public function previewToken(): string
	{
		$id = match (true) {
			$this->model instanceof Site => '',
			$this->model instanceof Page => $this->model->id() . $this->model->template(),
			default                      => throw new LogicException('Invalid model type')
		};

		return $this->model->kirby()->contentToken(
			$this->model,
			$id
		);
	}

	/**
	 * This method can only be applied to the "changes" version.
	 * It will copy all fields over to the "latest" version and delete
	 * this version afterwards.
	 */
	public function publish(Language|string $language = 'default'): void
	{
		$language = Language::ensure($language);

		// check if publishing is allowed
		VersionRules::publish($this, $language);

		// update the latest version
		$this->model->update(
			input: $this->read($language),
			languageCode: $language->code(),
			validate: true
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
			// make sure that the version exists
			VersionRules::read($this, $language);

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
	public function replace(
		array $fields,
		Language|string $language = 'default'
	): void {
		$language = Language::ensure($language);

		// check if replacing is allowed
		VersionRules::replace($this, $fields, $language);

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
		$language = Language::ensure($language);

		VersionRules::touch($this, $language);

		$this->model->storage()->touch($this->id, $language);
	}

	/**
	 * Updates the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function update(
		array $fields,
		Language|string $language = 'default'
	): void {
		$language = Language::ensure($language);

		// check if updating is allowed
		VersionRules::update($this, $fields, $language);

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

	/**
	 * Returns the preview URL with authentication for drafts
	 * @internal
	 */
	public function url(): string|null
	{
		if (
			($this->model instanceof Page || $this->model instanceof Site) === false
		) {
			throw new LogicException('Only pages and the site have a content preview URL');
		}

		$url = $this->model->blueprint()->preview();

		if ($url === false) {
			return null;
		}

		$url = match ($url) {
			true, null => $this->model->url(),
			default    => $url
		};

		$uri = new Uri($url);

		if ($this->model instanceof Page && $this->model->isDraft() === true) {
			$uri->query->_token = $this->previewToken();
		}

		if ($this->id->is('changes') === true) {
			$uri->query->_version = 'changes';
		}

		return $uri->toString();
	}
}
