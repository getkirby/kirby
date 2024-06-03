<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\Language;
use Kirby\Cms\Languages;
use Kirby\Cms\Page;

/**
 * Test Model to prototype the content and translation
 * mechanics
 */
class LabPage extends Page
{
	/**
	 * Returns the content for the default version and given language code
	 */
	public function content(string|null $languageCode = null): Content
	{
		return $this->version()->content($languageCode ?? 'current');
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

		$this->version()->create($content, 'default');

		return $this;
	}

	/**
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

		// go through all translations and create a default version for it
		foreach ($translations as $translation) {
			$language = Language::ensure($translation['code'] ?? 'default');
			$content  = $translation['content'] ?? [];

			// add the custom slug to the content array
			if (isset($translation['slug']) === true) {
				$content['slug'] = $translation['slug'] ?? null;
			}

			$this->version()->create($content, $language->code());
		}

		return $this;
	}

	/**
	 * Returns a single translation by language code
	 * If no code is specified the current translation is returned
	 *
	 * @throws \Kirby\Exception\NotFoundException If the language does not exist
	 */
	public function translation(
		string|null $languageCode = null
	): ContentTranslation|null {
		$languageCode ??= Language::ensure($languageCode)->code();
		return $this->translations()->find($languageCode);
	}

	/**
	 * Returns the translations collection
	 */
	public function translations(): ContentTranslations
	{
		return ContentTranslations::load(model: $this);
	}

}
