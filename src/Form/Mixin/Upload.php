<?php

namespace Kirby\Form\Mixin;

use Closure;
use Kirby\Api\Api;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;

trait Upload
{
	/**
	 * Sets the upload options for linked files
	 */
	protected mixed $uploads;

	public function upload(
		Api $api,
		array|false $params,
		Closure $map
	): array {
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

	protected function uploadParent(
		string|null $parentQuery = null
	): Site|Page|User|null {
		$parent = $this->model();

		if ($parentQuery) {
			$parent = $parent->query($parentQuery);
		}

		if ($parent instanceof File) {
			$parent = $parent->parent();
		}

		return $parent;
	}

	public function uploads(): array|false
	{
		$uploads = $this->uploads;

		if ($uploads === false) {
			return false;
		}

		$uploads = match (true) {
			is_string($uploads) => ['template' => $uploads],
			is_array($uploads)  => $uploads,
			default             => []
		};

		// add defaults
		$uploads = [
			'accept'   => '*',
			'parent'   => null,
			'template' => null,
			...$uploads
		];

		if (method_exists($this, 'image') === true) {
			$uploads['preview'] = $this->image();
		}

		if ($template = $uploads['template']) {
			// get parent object for upload target
			$parent = $this->uploadParent($uploads['parent']);

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

		return array_filter($uploads);
	}
}
