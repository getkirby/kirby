<?php

namespace Kirby\Content;

use Kirby\Cms\ModelWithContent;

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
	public function create(string $language, array $fields): void
	{
		$this->model->storage()->create($this->id, $language, $fields);
	}

	/**
	 * Deletes a version by language or for any language
	 */
	public function delete(string|null $language = null): void
	{
		// delete a single language
		if ($language !== null) {
			$this->model->storage()->delete($this->id, $language);
			return;
		}

		// delete all languages
		foreach ($this->model->kirby()->languages() as $language) {
			$this->model->storage()->delete($this->id, $language->code());
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

	public function move(string $fromLanguage, VersionId $toVersionId, string $toLanguage): void
	{
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
		return $this->model->storage()->read($this->id, $language);
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(string $language): void
	{
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
	public function update(string $language, array $fields): void
	{
		$this->model->storage()->update($this->id, $language, $fields);
	}
}
