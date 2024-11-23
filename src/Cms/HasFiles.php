<?php

namespace Kirby\Cms;

use Kirby\Uuid\Uuid;

/**
 * HasFiles
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait HasFiles
{
	/**
	 * The Files collection
	 */
	protected Files|array|null $files = null;

	/**
	 * Filters the Files collection by type audio
	 */
	public function audio(): Files
	{
		return $this->files()->filter('type', '==', 'audio');
	}

	/**
	 * Filters the Files collection by type code
	 */
	public function code(): Files
	{
		return $this->files()->filter('type', '==', 'code');
	}

	/**
	 * Creates a new file
	 *
	 * @param bool $move If set to `true`, the source will be deleted
	 */
	public function createFile(array $props, bool $move = false): File
	{
		$props = array_merge($props, [
			'parent' => $this,
			'url'    => null
		]);

		return File::create($props, $move);
	}

	/**
	 * Filters the Files collection by type documents
	 */
	public function documents(): Files
	{
		return $this->files()->filter('type', '==', 'document');
	}

	/**
	 * Returns a specific file by filename or the first one
	 */
	public function file(
		string|null $filename = null,
		string $in = 'files'
	): File|null {
		if ($filename === null) {
			return $this->$in()->first();
		}

		// find by global UUID
		if (Uuid::is($filename, 'file') === true) {
			return Uuid::for($filename, $this->$in())->model();
		}

		if (strpos($filename, '/') !== false) {
			$path     = dirname($filename);
			$filename = basename($filename);

			if ($page = $this->find($path)) {
				return $page->$in()->find($filename);
			}

			return null;
		}

		return $this->$in()->find($filename);
	}

	/**
	 * Returns the Files collection
	 */
	public function files(): Files
	{
		if ($this->files instanceof Files) {
			return $this->files;
		}

		return $this->files = Files::factory($this->inventory()['files'], $this);
	}

	/**
	 * Checks if the Files collection has any audio files
	 */
	public function hasAudio(): bool
	{
		return $this->audio()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any code files
	 */
	public function hasCode(): bool
	{
		return $this->code()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any document files
	 */
	public function hasDocuments(): bool
	{
		return $this->documents()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any files
	 */
	public function hasFiles(): bool
	{
		return $this->files()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any images
	 */
	public function hasImages(): bool
	{
		return $this->images()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any videos
	 */
	public function hasVideos(): bool
	{
		return $this->videos()->count() > 0;
	}

	/**
	 * Returns a specific image by filename or the first one
	 */
	public function image(string|null $filename = null): File|null
	{
		return $this->file($filename, 'images');
	}

	/**
	 * Filters the Files collection by type image
	 */
	public function images(): Files
	{
		return $this->files()->filter('type', '==', 'image');
	}

	/**
	 * Sets the Files collection
	 *
	 * @return $this
	 */
	protected function setFiles(array|null $files = null): static
	{
		if ($files !== null) {
			$this->files = Files::factory($files, $this);
		}

		return $this;
	}

	/**
	 * Filters the Files collection by type videos
	 */
	public function videos(): Files
	{
		return $this->files()->filter('type', '==', 'video');
	}
}
