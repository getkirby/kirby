<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Api;
use Kirby\Cms\File;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Closure;

/**
 * Upload functionality
 *
 * @since 6.0.0
 */
trait Upload
{
	/**
	 * Sets the upload options for linked files
	 */
	protected mixed $uploads;

	public function uploads(): mixed
	{
		return $this->uploads;
	}

	protected function setUploads(mixed $uploads = []): void
	{
		if ($uploads === false) {
			$this->uploads = false;
			return;
		}

		if (is_string($uploads) === true) {
			$uploads = ['template' => $uploads];
		}

		if (is_array($uploads) === false) {
			$uploads = [];
		}

		$uploads['accept'] = '*';

		if ($preview = $this->image) {
			$uploads['preview'] = $preview;
		}

		if ($template = $uploads['template'] ?? null) {
			// get parent object for upload target
			$parent = $this->uploadParent($uploads['parent'] ?? null);

			if ($parent === null) {
				throw new InvalidArgumentException(
					message: '"' . $uploads['parent'] . '" could not be resolved as a valid parent for the upload'
				);
			}

			$file = new File([
				'filename' => 'tmp',
				'parent'   => $parent,
				'template' => $template
			]);

			$uploads['accept'] = $file->blueprint()->acceptAttribute();
		}

		$this->uploads = $uploads;
	}

	public function upload(Api $api, mixed $params, Closure $map): mixed
	{
		if ($params === false) {
			throw new Exception(
				message: 'Uploads are disabled for this field'
			);
		}

		$parent = $this->uploadParent($params['parent'] ?? null);

		return $api->upload(function ($source, $filename) use ($parent, $params, $map) {
			$props = [
				'source'   => $source,
				'template' => $params['template'] ?? null,
				'filename' => $filename,
			];

			// move the source file from the temp dir
			$file = $parent->createFile($props, true);

			if ($file instanceof File === false) {
				throw new Exception(
					message: 'The file could not be uploaded'
				);
			}

			return $map($file, $parent);
		});
	}

	public function uploadParent(string|null $parentQuery = null): mixed
	{
		$parent = $this->model();

		if ($parentQuery) {
			$parent = $parent->query($parentQuery);
		}

		if ($parent instanceof File) {
			$parent = $parent->parent();
		}

		return $parent;
	}
}
