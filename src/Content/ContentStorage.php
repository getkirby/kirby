<?php

namespace Kirby\Content;

use Generator;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;

/**
 * Wrapper for the ContentStorageHandler to
 * bundle some business logic that should not
 * be included in the handlers themselves but
 * also not in the general code calling the storage
 * methods
 *
 * @internal
 * @since 4.0.0
 *
 * @package   Kirby Content
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class ContentStorage
{
	protected ContentStorageHandler $handler;

	public function __construct(
		protected ModelWithContent $model,
		string $handler = PlainTextContentStorageHandler::class
	) {
		$this->handler = new $handler($model);
	}

	/**
	 * Magic caller for handler methods
	 */
	public function __call(string $name, array $args): mixed
	{
		return $this->handler->$name(...$args);
	}

	/**
	 * Returns generator for all existing versions-languages combinations
	 *
	 * @return Generator<string|string>
	 * @todo 4.0.0 consider more descpritive name
	 */
	public function all(): Generator
	{
		foreach ($this->model->kirby()->languages()->codes() as $lang) {
			foreach ($this->dynamicVersions() as $versionId) {
				if ($this->exists($versionId, $lang) === true) {
					yield $versionId => $lang;
				}
			}
		}
	}

	/**
	 * Returns the absolute path to the content file
	 * @internal eventually should only exists in PlainTextContentStorage,
	 * 			 when not relying anymore on language helper
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 *
	 * @throws \Kirby\Exception\LogicException If the model type doesn't have a known content filename
	 */
	public function contentFile(
		VersionId $versionId,
		string $lang,
		bool $force = false
	): string {
		$lang = $this->language($lang, $force);
		return $this->handler->contentFile($versionId, $lang);
	}

	/**
	 * Adapts all versions when converting languages
	 * @internal
	 */
	public function convertLanguage(string $from, string $to): void
	{
		$from = $this->language($from, true);
		$to   = $this->language($to, true);

		foreach ($this->dynamicVersions() as $versionId) {
			$this->handler->move($versionId, $from, $versionId, $to);
		}
	}

	/**
	 * Creates a new version
	 *
	 * @param string|null $lang Code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 */
	public function create(
		VersionId $versionId,
		string|null $lang,
		array $fields
	): void {
		$lang = $this->language($lang);
		$this->handler->create($versionId, $lang, $fields);
	}

	/**
	 * Returns the default version identifier for the model
	 * @internal
	 */
	public function defaultVersion(): VersionId
	{
		if (
			$this->model instanceof Page === true &&
			$this->model->isDraft() === true
		) {
			return VersionId::changes();
		}

		return VersionId::published();
	}

	/**
	 * Deletes an existing version in an idempotent way if it was already deleted
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	public function delete(
		VersionId $versionId,
		string|null $lang = null,
		bool $force = false
	): void {
		$lang = $this->language($lang, $force);
		$this->handler->delete($versionId, $lang);
	}

	/**
	 * Deletes all versions when deleting a language
	 * @internal
	 */
	public function deleteLanguage(string|null $lang): void
	{
		$lang = $this->language($lang, true);

		foreach ($this->dynamicVersions() as $version) {
			$this->handler->delete($version, $lang);
		}
	}

	/**
	 * Returns all versions availalbe for the model that can be updated
	 * @internal
	 */
	public function dynamicVersions(): array
	{
		$versions = [VersionId::changes()];

		if (
			$this->model instanceof Page === false ||
			$this->model->isDraft() === false
		) {
			$versions[] = VersionId::published();
		}

		return $versions;
	}

	/**
	 * Checks if a version exists
	 *
	 * @param string|null $lang Code `'default'` in a single-lang installation;
	 *                          checks for "any language" if not provided
	 */
	public function exists(
		VersionId $versionId,
		string|null $lang
	): bool {
		if ($lang !== null) {
			$lang = $this->language($lang);
		}

		return $this->handler->exists($versionId, $lang);
	}

	/**
	 * Returns the modification timestamp of a version
	 * if it exists
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	public function modified(
		VersionId $versionId,
		string|null $lang = null
	): int|null {
		$lang = $this->language($lang);
		return $this->handler->modified($versionId, $lang);
	}

	/**
	 * Returns the stored content fields
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @return array<string, string>
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function read(
		VersionId $versionId,
		string|null $lang = null
	): array {
		$lang = $this->language($lang);
		$this->ensureExistingVersion($versionId, $lang);
		return $this->handler->read($versionId, $lang);
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(
		VersionId $versionId,
		string|null $lang = null
	): void {
		$lang = $this->language($lang);
		$this->ensureExistingVersion($versionId, $lang);
		$this->handler->touch($versionId, $lang);
	}

	/**
	 * Touches all versions of a language
	 * @internal
	 */
	public function touchLanguage(string|null $lang): void
	{
		$lang = $this->language($lang, true);

		foreach ($this->dynamicVersions() as $version) {
			if ($this->exists($version, $lang) === true) {
				$this->handler->touch($version, $lang);
			}
		}
	}

	/**
	 * Updates the content fields of an existing version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function update(
		VersionId $versionId,
		string|null $lang = null,
		array $fields = []
	): void {
		$lang = $this->language($lang);
		$this->ensureExistingVersion($versionId, $lang);
		$this->handler->update($versionId, $lang, $fields);
	}

	/**
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	protected function ensureExistingVersion(
		VersionId $versionId,
		string $lang
	): void {
		if ($this->exists($versionId, $lang) !== true) {
			throw new NotFoundException('Version "' . $versionId->value . ' (' . $lang . ')" does not already exist');
		}
	}

	/**
	 * Converts a "user-facing" language code to a "raw" language code to be
	 * used for storage
	 *
	 * @param bool $force If set to `true`, the language code is not validated
	 * @return string Language code
	 */
	protected function language(
		string|null $languageCode = null,
		bool $force = false
	): string {
		// in force mode, use the provided language code even in single-lang for
		// compatibility with the previous behavior in `$model->contentFile()`
		if ($force === true) {
			return $languageCode ?? 'default';
		}

		// in multi-lang, …
		if ($this->model->kirby()->multilang() === true) {
			// look up the actual language object if possible
			$language = $this->model->kirby()->language($languageCode);

			// validate the language code
			if ($language === null) {
				throw new InvalidArgumentException('Invalid language: ' . $languageCode);
			}

			return $language->code();
		}

		// otherwise use hardcoded "default" code for single lang
		return 'default';
	}
}
