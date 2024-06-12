<?php

namespace Kirby\Content;

use Kirby\Cms\Collection;
use Kirby\Cms\Helpers;
use Kirby\Cms\Language;
use Kirby\Cms\Page;

/**
 * Test Model to prototype the content and translation
 * mechanics
 */
class LabPage extends Page
{
	/**
	 * Creates a new instance with the same
	 * initial properties
	 *
	 * @todo eventually refactor without need of propertyData
	 */
	public function clone(array $props = []): static
	{
		$this->storage = MemoryContentStorageHandler::from($this->storage);
		return parent::clone($props);
	}

	/**
	 * Returns the content for the default version and given language code
	 */
	public function content(string|null $languageCode = null): Content
	{
		return $this->version()->content($languageCode ?? 'current');
	}

	/**
	 * @deprecated since 5.0.0 Use `::version()->read()` instead
	 */
	public function readContent(string|null $languageCode = null): array
	{
		Helpers::deprecated('`$model->readContent()` has been deprecated. Use `$model->version()->read()` instead.', 'model-read-content');

		return $this->version()->read($languageCode ?? 'current');
	}

	/**
	 * @deprecated since 5.0.0 Use `::version()->save()` instead
	 */
	public function save(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('`$model->save()` has been deprecated. Use `$model->version()->save()` instead.', 'model-save');

		$clone = $this->clone();
		$clone->version()->save($data ?? [], $languageCode ?? 'current', $overwrite);
		return $clone;
	}

	/**
	 * @deprecated since 5.0.0 Use `::version()->save()` instead
	 */
	protected function saveContent(
		array|null $data = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('`$model->saveContent()` has been deprecated. Use `$model->version()->save()` instead.', 'model-save-content');

		$clone = $this->clone();
		$clone->version()->save($data ?? [], 'current', $overwrite);
		return $clone;
	}

	/**
	 * @deprecated since 5.0.0 Use `::version()->save()` instead
	 */
	protected function saveTranslation(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('`$model->saveTranslation()` has been deprecated. Use `$model->version()->save()` instead.', 'model-save-translation');

		$clone = $this->clone();
		$clone->version()->save($data ?? [], $languageCode ?? 'current', $overwrite);
		return $clone;
	}

	/**
	 * Sets the content when initializing a model manually.
	 * This will switch to the in memory storage to keep the
	 * content there instead of on disk or in a database.
	 * The content will be created for the default version and language
	 */
	protected function setContent(array|null $content = null): static
	{
		// don't set anything if there's no content
		if ($content === null) {
			return $this;
		}

		$this->storage = new MemoryContentStorageHandler(
			model: $this
		);

		Translation::create(
			model: $this,
			version: $this->version(),
			language: Language::ensure('default'),
			fields: $content
		);

		return $this;
	}

	/**
	 * Stores in-memory translations for the model if they
	 * are passed to the constructor with the translations prop.
	 */
	protected function setTranslations(array|null $translations = null): static
	{
		// don't set anything if there's no content
		if ($translations === null) {
			return $this;
		}

		// switch to in-memory content storage
		$this->storage = new MemoryContentStorageHandler(
			model: $this
		);

		Translations::create(
			model: $this,
			version: $this->version(),
			translations: $translations
		);

		return $this;
	}

	/**
	 * Returns a single translation by language code
	 * If no code is specified the current translation is returned
	 */
	public function translation(
		string|null $languageCode = null
	): Translation|null {
		return $this->translations()->find($languageCode ?? 'current');
	}

	/**
	 * Returns the translations collection
	 */
	public function translations(): Translations
	{
		return Translations::load(
			model: $this,
			version: $this->version()
		);
	}

	/**
	 * @deprecated since 5.0.0 Use `::version()->save()` instead
	 */
	public function writeContent(array $data, string|null $languageCode = null): bool
	{
		Helpers::deprecated('`$model->writeContent()` has been deprecated. Use `$model->version()->save()` instead.', 'model-write-content');

		$this->clone()->version()->save($data, $languageCode ?? 'current', true);
		return true;
	}
}
