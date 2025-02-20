<?php

namespace Kirby\Cms;

use Kirby\Content\Content;
use Kirby\Content\MemoryStorage;
use Kirby\Content\Storage;
use Kirby\Content\Translation;
use Kirby\Content\Translations;
use Kirby\Content\VersionId;

trait NewModelFixes
{
	/**
	 * Move the model to a new storage instance/type
	 * @since 5.0.0
	 */
	public function changeStorage(Storage|string $toStorage): static
	{
		if (is_string($toStorage) === true) {
			$toStorage = new $toStorage($this);
		}

		$this->storage()->copyAll(to: $toStorage);
		$this->storage = $toStorage;
		return $this;
	}

	public function clone(array $props = []): static
	{
		$props = array_replace_recursive($this->propertyData, $props);
		$clone = new static($props);

		// Move the clone to a new instance of the same storage class
		// The storage classes might need to rely on the model instance
		// and thus we need to make sure that the cloned object is
		// passed on to the new storage instance
		$storage = $this->storage()::class;
		$clone->changeStorage($storage);

		return $clone;
	}

	public function content(string|null $languageCode = null): Content
	{
		// get the targeted language
		$language  = Language::ensure($languageCode ?? 'current');
		$versionId = VersionId::$render ?? VersionId::latest();
		$version   = $this->version($versionId);

		if ($version->exists($language) === true) {
			return $version->content($language);
		}

		return $this->version()->content($language);
	}

	/**
	 * Converts model to new blueprint
	 * incl. its content for all translations
	 */
	protected function convertTo(string $blueprint): static
	{
		// first close object with new blueprint as template
		$new = $this->clone(['template' => $blueprint]);

		// get versions
		$latest  = $this->version(VersionId::latest());
		$changes = $this->version(VersionId::changes());

		foreach (Languages::ensure() as $language) {
			// delete changes
			$changes->delete($language);

			// skip non-existing versions
			if ($latest->exists($language) === false) {
				continue;
			}

			// convert the content to the new blueprint
			$content = $latest->content($language)->convertTo($blueprint);

			// delete the old text file
			$latest->delete($language);

			// save to re-create the content file
			// with the converted/updated content
			$new->version()->save($content, $language);
		}

		return $new;
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 */
	public function save(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		// create a clone to avoid modifying the original
		$clone = $this->clone();

		// move the old model into memory
		$this->changeStorage(MemoryStorage::class);

		// update the clone
		$clone->version()->save(
			$data ?? [],
			$languageCode ?? 'default',
			$overwrite
		);

		return $clone;
	}

	/**
	 * @deprecated 5.0.0 Use $model->save() instead
	 */
	protected function saveContent(
		array|null $data = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('$model->saveContent() is deprecated. Use $model->save() instead.');
		return $this->save($data, 'default', $overwrite);
	}

	/**
	 * @deprecated 5.0.0 Use $model->save() instead
	 */
	protected function saveTranslation(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('$model->saveTranslation() is deprecated. Use $model->save() instead.');
		return $this->save($data, $languageCode ?? 'default', $overwrite);
	}

	protected function setContent(array|null $content = null): static
	{
		if ($content === null) {
			return $this;
		}

		$this->changeStorage(MemoryStorage::class);
		$this->version()->save($content, 'default');

		return $this;
	}

	protected function setTranslations(array|null $translations = null): static
	{
		if ($translations === null) {
			return $this;
		}

		$this->changeStorage(MemoryStorage::class);

		Translations::create(
			model: $this,
			version: $this->version(),
			translations: $translations
		);

		return $this;
	}

	/**
	 * @todo Change return type to Translation once the refactoring is done
	 */
	public function translation(
		string|null $languageCode = null
	): Translation|null {
		$language = Language::ensure($languageCode ?? 'current');

		return new Translation(
			model: $this,
			version: $this->version(),
			language: $language
		);
	}

	public function translations(): Translations
	{
		return Translations::load(
			model: $this,
			version: $this->version()
		);
	}
}
