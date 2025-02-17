<?php

namespace Kirby\Cms;

use Kirby\Content\ContentTranslation;
use Kirby\Content\MemoryStorage;
use Kirby\Content\Storage;
use Kirby\Content\Translation;
use Kirby\Content\VersionId;

class NewPage extends Page
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
		$language  = Language::ensure($languageCode ?? 'default');
		$versionId = VersionId::$render ?? VersionId::latest();
		$version   = $this->version($versionId);

		if ($version->exists($language) === true) {
			return $version->content($language);
		}

		return $this->version()->content($language);
	}

	public function moveToStorage(Storage $toStorage): static
	{
		$currentStorage = $this->storage();

		// copy all versions to the given storage instance
		foreach ($currentStorage->all() as $versionId => $language) {
			$currentStorage->copy(
				fromVersionId: $versionId,
				fromLanguage: $language,
				toStorage: $toStorage
			);
		}

		$this->storage = $toStorage;
		return $this;
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

		foreach ($translations as $props) {
			// skip invalid translations without a language code
			if (isset($props['code']) === false) {
				continue;
			}

			// skip invalid translations without a matching language object
			if (!$language = Language::ensure($props['code'])) {
				continue;
			}

			$this->version()->save($props['content'] ?? [], $language);
		}

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

	public function translations(): Collection
	{
		$translations = new Collection();

		foreach (Languages::ensure() as $language) {
			$translation = $this->translation($language->code());
			$translations->data[$translation->code()] = $translation;
		}

		return $translations;
	}
}
