<?php

namespace Kirby\Cms;

use Kirby\Content\ContentTranslation;
use Kirby\Content\MemoryStorage;
use Kirby\Content\Storage;
use Kirby\Content\Translation;
use Kirby\Content\Translations;
use Kirby\Content\VersionId;

trait NewModelFixes
{
	public function clone(array $props = []): static
	{
		$clone = new static(array_replace_recursive($this->propertyData, $props));
		$class = get_class($this->storage());

		// Move the clone to a new instance of the same storage class
		// The storage classes might need to rely on the model instance
		// and thus we need to make sure that the cloned object is
		// passed on to the new storage instance
		$clone->moveToStorage(new $class($clone));

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

	public function moveToStorage(Storage $toStorage): static
	{
		$this->storage()->copyAll(to: $toStorage);
		$this->storage = $toStorage;
		return $this;
	}

	public function save(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		// create a clone to avoid modifying the original
		$clone = $this->clone();

		// move the old model into memory
		$this->moveToStorage(new MemoryStorage($this));

		// update the clone
		$clone->version()->save($data ?? [], $languageCode ?? 'default', $overwrite);

		return $clone;
	}

	protected function saveContent(
		array|null $data = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('$model->saveContent() is deprecated. Use $model->save() instead.');
		return $this->save($data, 'default', $overwrite);
	}

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

		$this->moveToStorage(new MemoryStorage($this));
		$this->version()->save($content, 'default');

		return $this;
	}

	protected function setTranslations(array|null $translations = null): static
	{
		if ($translations === null) {
			return $this;
		}

		$this->moveToStorage(new MemoryStorage($this));

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
	): ContentTranslation|null {
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
