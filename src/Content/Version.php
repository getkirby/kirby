<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Form;
use Kirby\Http\Uri;
use Kirby\Toolkit\Str;

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
			// merge the fields with the default language
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
			normalize: false
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

		// make sure that an older version does not exist in the cache
		VersionCache::remove($this, $language);
	}

	/**
	 * Deletes a version for a specific language
	 */
	public function delete(Language|string $language = 'default'): void
	{
		if ($language === '*') {
			foreach (Languages::ensure() as $language) {
				$this->delete($language);
			}

			return;
		}

		$language = Language::ensure($language);

		// check if deleting is allowed
		VersionRules::delete($this, $language);

		$this->model->storage()->delete($this->id, $language);

		// untrack the changes if the version does no longer exist
		// in any of the available languages
		if ($this->id->is(VersionId::changes()) === true && $this->exists('*') === false) {
			(new Changes())->untrack($this->model);
		}

		// Remove the version from the cache
		VersionCache::remove($this, $language);
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

		$a = Form::for(
			model: $this->model,
			props: [
				'language' => $language->code(),
				'values'   => $a,
			]
		)->values();

		$b = Form::for(
			model: $this->model,
			props: [
				'language' => $language->code(),
				'values'   => $b
			]
		)->values();

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

		// remove both versions from the cache
		VersionCache::remove($fromVersion, $fromLanguage);
		VersionCache::remove($toVersion, $toLanguage);
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
		$fields = $this->convertFieldNamesToLowerCase($fields);

		// ignore all fields with null values
		return array_filter($fields, fn ($field) => $field !== null);
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
	 * of draft and version previews
	 * @internal
	 */
	public function previewToken(): string
	{
		if ($this->model instanceof Site) {
			// the site itself does not render; its preview is the home page
			$homePage = $this->model->homePage();

			if ($homePage === null) {
				throw new NotFoundException('The home page does not exist');
			}

			return $homePage->version($this->id)->previewToken();
		}

		if (($this->model instanceof Page) === false) {
			throw new LogicException('Invalid model type');
		}

		return $this->previewTokenFromUrl($this->model->url())
			?? throw new LogicException('Cannot produce local preview token for model');
	}

	/**
	 * Returns a verification token for the authentication
	 * of draft and version previews from a raw URL
	 * if the URL comes from the same site
	 */
	protected function previewTokenFromUrl(string $url): string|null
	{
		$localPrefix = $this->model->kirby()->url('base') . '/';

		// normalize homepage URLs to have a trailing slash
		// to make the following logic work with those as well
		if ($url . '/' === $localPrefix) {
			$url .= '/';
		}

		if (Str::startsWith($url, $localPrefix) === false) {
			return null;
		}

		// get rid of all modifiers after the path
		$uri = new Uri($url);
		$uri->fragment = null;
		$uri->params   = null;
		$uri->query    = null;

		$data = [
			'uri'       => Str::after($uri->toString(), $localPrefix),
			'versionId' => $this->id->value()
		];

		$token = $this->model->kirby()->contentToken(
			null,
			json_encode($data)
		);

		return substr($token, 0, 10);
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
		$this->model = $this->model->update(
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

			$fields = VersionCache::get($this, $language);

			if ($fields === null) {
				$fields = $this->model->storage()->read($this->id, $language);
				$fields = $this->prepareFieldsAfterRead($fields, $language);

				if ($fields !== null) {
					VersionCache::set($this, $language, $fields);
				}
			}

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

		// remove the version from the cache to read
		// a fresh version next time
		VersionCache::remove($this, $language);
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

		// remove the version from the cache to read
		// a fresh version next time
		VersionCache::remove($this, $language);
	}

	/**
	 * Returns the preview URL with authentication for drafts and versions
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

		// preview was disabled
		if ($url === false) {
			return null;
		}

		// we only need to add a token for draft and changes previews
		if (
			($this->model instanceof Site || $this->model->isDraft() === false) &&
			$this->id->is('changes') === false
		) {
			return match (true) {
				is_string($url) => $url,
				default         => $this->model->url()
			};
		}

		// check if the URL was customized
		if (is_string($url) === true) {
			return $this->urlFromOption($url);
		}

		// it wasn't, use the safer/more reliable model-based preview token
		return $this->urlWithQueryParams($this->model->url(), $this->previewToken());
	}

	/**
	 * Returns the preview URL based on an arbitrary URL from
	 * the blueprint option
	 */
	protected function urlFromOption(string $url): string
	{
		// try to determine a token for a local preview
		// (we cannot determine the token for external previews)
		if ($token = $this->previewTokenFromUrl($url)) {
			return $this->urlWithQueryParams($url, $token);
		}

		// fall back to the URL as defined in the blueprint
		return $url;
	}

	/**
	 * Assembles the preview URL with the added `_token` and `_version`
	 * query params, no matter if the base URL already contains query params
	 */
	protected function urlWithQueryParams(string $baseUrl, string $token): string
	{
		$uri = new Uri($baseUrl);
		$uri->query->_token = $token;

		if ($this->id->is('changes') === true) {
			$uri->query->_version = 'changes';
		}

		return $uri->toString();
	}
}
