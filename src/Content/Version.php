<?php

namespace Kirby\Content;

use Kirby\Cms\ModelWithContent;
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
			data:   $this->model->storage()->read($this->id, $language),
		);
	}

	/**
	 * Creates a new version for the given language
	 */
	public function create(array $fields, string $language = 'default'): void
	{
		$this->model->storage()->create($this->id, $language, $fields);
	}

	/**
	 * Deletes a version by language or for any language
	 */
	public function delete(string|null $language = null): void
	{
		// delete all languages
		if ($language === null) {
			foreach ($this->model->kirby()->languages() as $language) {
				$this->model->storage()->delete($this->id, $language->code());
			}
		}

		// delete the default language in single-language mode
		if ($this->model->kirby()->multilang() === false) {
			$this->model->storage()->delete($this->id, 'default');
			return;
		}

		// delete a single language
		$this->model->storage()->delete($this->id, $language);
		return;
	}

	/**
	 * Ensure that the version exists and otherwise
     * throw an exception
	 *
	 * @throws \Kirby\Exception\NotFoundException if the version does not exist
	 */
	public function ensure(
		string $language = 'default'
	): void {
		if ($this->exists($language) !== true) {
			throw new NotFoundException('Version "' . $this->id . ' (' . $language . ')" does not already exist');
		}
	}

	/**
	 * Checks if a version exists for the given language
	 */
	public function exists(string $language = 'default'): bool
	{
		return $this->model->storage()->exists($this->id, $language);
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
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	public function modified(
		string $language = 'default'
	): int|null {
		$this->ensure($language);
		return $this->model->storage()->modified($this->id, $language);
	}

	/**
	 * Moves the version to a new language and/or version
	 */
	public function move(string $fromLanguage, VersionId $toVersionId, string $toLanguage): void
	{
		$this->ensure($fromLanguage);
		$this->model->storage()->move($this->id, $fromLanguage, $toVersionId, $toLanguage);
	}

	/**
	 * Returns the stored content fields
     *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @return array<string, string>
	 */
	public function read(string $language = 'default'): array
	{
		$this->ensure($language);
		return $this->model->storage()->read($this->id, $language);
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(string|null $language = null): void
	{
		// touch all languages
		if ($language === null) {
			foreach ($this->model->kirby()->languages() as $language) {
				$this->touch($language->code());
			}
		}

		// make sure the version exists
		$this->ensure($language);

		// touch the default language in single-language mode
		if ($this->model->kirby()->multilang() === false) {
			$this->model->storage()->touch($this->id, 'default');
			return;
		}

		// touch a single language
		$this->model->storage()->touch($this->id, $language);
	}

	/**
	 * Updates the content fields of an existing version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function update(array $fields, string $language = 'default'): void
	{
		$this->ensure($language);
		$this->model->storage()->update($this->id, $language, $fields);
	}
}
