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
	 *
	 * @var \Kirby\Cms\Files
	 */
	protected $files;

	/**
	 * Filters the Files collection by type audio
	 *
	 * @return \Kirby\Cms\Files
	 */
	public function audio()
	{
		return $this->files()->filter('type', '==', 'audio');
	}

	/**
	 * Filters the Files collection by type code
	 *
	 * @return \Kirby\Cms\Files
	 */
	public function code()
	{
		return $this->files()->filter('type', '==', 'code');
	}

	/**
	 * Returns a list of file ids
	 * for the toArray method of the model
	 *
	 * @return array
	 */
	protected function convertFilesToArray(): array
	{
		return $this->files()->keys();
	}

	/**
	 * Creates a new file
	 *
	 * @param array $props
	 * @param bool $move If set to `true`, the source will be deleted
	 * @return \Kirby\Cms\File
	 */
	public function createFile(array $props, bool $move = false)
	{
		$props = array_merge($props, [
			'parent' => $this,
			'url'    => null
		]);

		return File::create($props, $move);
	}

	/**
	 * Filters the Files collection by type documents
	 *
	 * @return \Kirby\Cms\Files
	 */
	public function documents()
	{
		return $this->files()->filter('type', '==', 'document');
	}

	/**
	 * Returns a specific file by filename or the first one
	 *
	 * @param string|null $filename
	 * @param string $in
	 * @return \Kirby\Cms\File|null
	 */
	public function file(string $filename = null, string $in = 'files')
	{
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
	 *
	 * @return \Kirby\Cms\Files
	 */
	public function files()
	{
		if ($this->files instanceof Files) {
			return $this->files;
		}

		return $this->files = Files::factory($this->inventory()['files'], $this);
	}

	/**
	 * Checks if the Files collection has any audio files
	 *
	 * @return bool
	 */
	public function hasAudio(): bool
	{
		return $this->audio()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any code files
	 *
	 * @return bool
	 */
	public function hasCode(): bool
	{
		return $this->code()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any document files
	 *
	 * @return bool
	 */
	public function hasDocuments(): bool
	{
		return $this->documents()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any files
	 *
	 * @return bool
	 */
	public function hasFiles(): bool
	{
		return $this->files()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any images
	 *
	 * @return bool
	 */
	public function hasImages(): bool
	{
		return $this->images()->count() > 0;
	}

	/**
	 * Checks if the Files collection has any videos
	 *
	 * @return bool
	 */
	public function hasVideos(): bool
	{
		return $this->videos()->count() > 0;
	}

	/**
	 * Returns a specific image by filename or the first one
	 *
	 * @param string|null $filename
	 * @return \Kirby\Cms\File|null
	 */
	public function image(string $filename = null)
	{
		return $this->file($filename, 'images');
	}

	/**
	 * Filters the Files collection by type image
	 *
	 * @return \Kirby\Cms\Files
	 */
	public function images()
	{
		return $this->files()->filter('type', '==', 'image');
	}

	/**
	 * Sets the Files collection
	 *
	 * @param \Kirby\Cms\Files|null $files
	 * @return $this
	 */
	protected function setFiles(array $files = null)
	{
		if ($files !== null) {
			$this->files = Files::factory($files, $this);
		}

		return $this;
	}

	/**
	 * Filters the Files collection by type videos
	 *
	 * @return \Kirby\Cms\Files
	 */
	public function videos()
	{
		return $this->files()->filter('type', '==', 'video');
	}
}
