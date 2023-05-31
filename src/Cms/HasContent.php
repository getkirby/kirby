<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Uuid\Uuids;

/**
 * HasContent
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait HasContent
{
	public Content|null $content = null;
	public Collection|null $translations = null;

	/**
	 * Returns the content
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language for the given code does not exist
	 */
	public function content(string|null $languageCode = null): Content
	{
		// single language support
		if ($this->kirby()->multilang() === false) {
			if ($this->content instanceof Content) {
				return $this->content;
			}

			// don't normalize field keys (already handled by the `Data` class)
			return $this->content = new Content($this->readContent(), $this, false);
		}

		// get the targeted language
		$language = $this->kirby()->language($languageCode);

		// stop if the language does not exist
		if ($language === null) {
			throw new InvalidArgumentException('Invalid language: ' . $languageCode);
		}

		// only fetch from cache for the current language
		if ($languageCode === null && $this->content instanceof Content) {
			return $this->content;
		}

		// get the translation by code
		$translation = $this->translation($language->code());

		// don't normalize field keys (already handled by the `ContentTranslation` class)
		$content = new Content($translation->content(), $this, false);

		// only store the content for the current language
		if ($languageCode === null) {
			$this->content = $content;
		}

		return $content;
	}

	/**
	 * Returns the absolute path to the content file
	 *
	 * @internal
	 * @throws \Kirby\Exception\InvalidArgumentException If the language for the given code does not exist
	 */
	public function contentFile(string|null $languageCode = null, bool $force = false): string
	{
		$extension = $this->contentFileExtension();
		$directory = $this->contentFileDirectory();
		$filename  = $this->contentFileName();

		// overwrite the language code
		if ($force === true) {
			if (empty($languageCode) === false) {
				return $directory . '/' . $filename . '.' . $languageCode . '.' . $extension;
			}

			return $directory . '/' . $filename . '.' . $extension;
		}

		// add and validate the language code in multi language mode
		if ($this->kirby()->multilang() === true) {
			if ($language = $this->kirby()->languageCode($languageCode)) {
				return $directory . '/' . $filename . '.' . $language . '.' . $extension;
			}

			throw new InvalidArgumentException('Invalid language: ' . $languageCode);
		}

		return $directory . '/' . $filename . '.' . $extension;
	}

	/**
	 * Returns an array with all content files
	 */
	public function contentFiles(): array
	{
		if ($this->kirby()->multilang() === true) {
			$files = [];
			foreach ($this->kirby()->languages()->codes() as $code) {
				$files[] = $this->contentFile($code);
			}
			return $files;
		}

		return [
			$this->contentFile()
		];
	}

	/**
	 * Prepares the content that should be written
	 * to the text file
	 *
	 * @internal
	 */
	public function contentFileData(array $data, string|null $languageCode = null): array
	{
		return $data;
	}

	/**
	 * Returns the absolute path to the
	 * folder in which the content file is
	 * located
	 *
	 * @internal
	 */
	public function contentFileDirectory(): string|null
	{
		return $this->root();
	}

	/**
	 * Returns the extension of the content file
	 *
	 * @internal
	 */
	public function contentFileExtension(): string
	{
		return $this->kirby()->contentExtension();
	}

	/**
	 * Needs to be declared by the final model
	 *
	 * @internal
	 */
	abstract public function contentFileName(): string;

	/**
	 * Read the content from the content file
	 *
	 * @internal
	 */
	public function readContent(string|null $languageCode = null): array
	{
		$file = $this->contentFile($languageCode);

		// only if the content file really does not exist, it's ok
		// to return empty content. Otherwise this could lead to
		// content loss in case of file reading issues
		if (file_exists($file) === false) {
			return [];
		}

		return Data::read($file);
	}

	/**
	 * Save the single language content
	 */
	protected function saveContent(array|null $data = null, bool $overwrite = false): static
	{
		// create a clone to avoid modifying the original
		$clone = $this->clone();

		// merge the new data with the existing content
		$clone->content()->update($data, $overwrite);

		// send the full content array to the writer
		$clone->writeContent($clone->content()->toArray());

		return $clone;
	}

	/**
	 * Save a translation
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language for the given code does not exist
	 */
	protected function saveTranslation(array|null $data = null, string|null $languageCode = null, bool $overwrite = false): static
	{
		// create a clone to not touch the original
		$clone = $this->clone();

		// fetch the matching translation and update all the strings
		$translation = $clone->translation($languageCode);

		if ($translation === null) {
			throw new InvalidArgumentException('Invalid language: ' . $languageCode);
		}

		// get the content to store
		$content      = $translation->update($data, $overwrite)->content();
		$kirby        = $this->kirby();
		$languageCode = $kirby->languageCode($languageCode);

		// remove all untranslatable fields
		if ($languageCode !== $kirby->defaultLanguage()->code()) {
			foreach ($this->blueprint()->fields() as $field) {
				if (($field['translate'] ?? true) === false) {
					$content[strtolower($field['name'])] = null;
				}
			}

			// remove UUID for non-default languages
			if (Uuids::enabled() === true && isset($content['uuid']) === true) {
				$content['uuid'] = null;
			}

			// merge the translation with the new data
			$translation->update($content, true);
		}

		// send the full translation array to the writer
		$clone->writeContent($translation->content(), $languageCode);

		// reset the content object
		$clone->content = null;

		// return the updated model
		return $clone;
	}

	/**
	 * Sets the Content object
	 */
	protected function setContent(array|null $content = null): static
	{
		if ($content !== null) {
			$content = new Content($content, $this);
		}

		$this->content = $content;
		return $this;
	}

	/**
	 * Create the translations collection from an array
	 */
	protected function setTranslations(array|null $translations = null): static
	{
		if ($translations !== null) {
			$this->translations = new Collection();

			foreach ($translations as $props) {
				$props['parent'] = $this;
				$translation = new ContentTranslation($props);
				$this->translations->data[$translation->code()] = $translation;
			}
		}

		return $this;
	}

	/**
	 * Returns a single translation by language code
	 * If no code is specified the current translation is returned
	 */
	public function translation(string|null $languageCode = null): ContentTranslation
	{
		if ($language = $this->kirby()->language($languageCode)) {
			return $this->translations()->find($language->code());
		}

		return null;
	}

	/**
	 * Returns the translations collection
	 */
	public function translations(): Collection
	{
		if ($this->translations !== null) {
			return $this->translations;
		}

		$this->translations = new Collection();

		foreach ($this->kirby()->languages() as $language) {
			$translation = new ContentTranslation([
				'parent' => $this,
				'code'   => $language->code(),
			]);

			$this->translations->data[$translation->code()] = $translation;
		}

		return $this->translations;
	}

	/**
	 * Low level data writer method
	 * to store the given data on disk or anywhere else
	 *
	 * @internal
	 */
	public function writeContent(array $data, string|null $languageCode = null): bool
	{
		return Data::write(
			$this->contentFile($languageCode),
			$this->contentFileData($data, $languageCode)
		);
	}
}
