<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;

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
			data:   $this->model->storage()->read($this->id, $this->language($language)),
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
		return $this->model->storage()->contentFile($this->id, $this->language($language));
	}

	/**
	 * Creates a new version for the given language
	 * @todo Convert to a static method that creates the version initially with all relevant languages
	 *
	 * @param array<string, string> $fields Content fields
	 */
	public function create(array $fields, Language|string $language = 'default'): void
	{
		$this->model->storage()->create($this->id, $this->language($language), $fields);
	}

	/**
	 * Deletes a version with all its languages
	 */
	public function delete(): void
	{
		// delete the default language in single-language mode
		if ($this->model->kirby()->multilang() === false) {
			$this->model->storage()->delete($this->id, $this->language('default'));
			return;
		}

		// delete all languages
		foreach ($this->model->kirby()->languages() as $language) {
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
	): bool {
		return $this->model->storage()->ensure($this->id, $this->language($language));
	}

	/**
	 * Checks if a version exists for the given language
	 */
	public function exists(Language|string $language = 'default'): bool
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
	 * Converts a "user-facing" language code or Language object
	 * to a `Language` object to be used in storage methods
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException if the language code does not match a valid language
	 */
	protected function language(
		Language|string|null $languageCode = null,
	): Language {
		if ($languageCode instanceof Language) {
			return $languageCode;
		}

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
		Language|string $language = 'default'
	): int|null {
		if ($this->exists($language) === true) {
			return $this->model->storage()->modified($this->id, $this->language($language));
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
			fromLanguage: $this->language($fromLanguage),
			toVersionId: $toVersionId,
			toLanguage: $this->language($toLanguage)
		);
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function read(Language|string $language = 'default'): array
	{
		$this->ensure($language);
		return $this->model->storage()->read($this->id, $this->language($language));
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(Language|string $language = 'default'): void
	{
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
	public function update(array $fields, Language|string $language = 'default'): void
	{
		$this->ensure($language);
		$this->model->storage()->update($this->id, $this->language($language), $fields);
	}
}
