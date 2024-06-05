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

		Translation::create(
			model: $this,
			version: $this->version(),
			language: Language::ensure('default'),
			fields: $content,
		);

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
}
