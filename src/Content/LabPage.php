<?php

namespace Kirby\Content;

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

}
