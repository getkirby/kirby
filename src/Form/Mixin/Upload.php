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
use Kirby\Reflection\Attributes\Prop;

/**
 * Provides upload options and file linking functionality
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Upload
{
	/**
	 * Sets the upload options for linked files
	 */
	#[Prop(computed: true)]
	protected mixed $uploads = null;

	/**
	 * Uploads and creates files via the API upload handler
	 * @codeCoverageIgnore
	 */
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

		return $api->upload(
			callback: function ($source, $filename, $template) use ($parent, $map) {
				$props = [
					'source'   => $source,
					'template' => $template,
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
			},
			template: $params['template'] ?? null
		);
	}

	protected function uploadParent(
		string|null $parentQuery = null
	): Site|Page|User {
		$parent = $this->model();

		if ($parentQuery) {
			$parent = $parent->query($parentQuery);
		}

		if ($parent instanceof File) {
			$parent = $parent->parent();
		}

		// query results that cannot hold files are no valid parent
		if (
			$parent instanceof Site ||
			$parent instanceof Page ||
			$parent instanceof User
		) {
			return $parent;
		}

		throw new InvalidArgumentException(
			message: '"' . $parentQuery . '" could not be resolved as a valid parent for the upload'
		);
	}

	/**
	 * Returns the normalized upload options
	 * or `false` if uploads are disabled
	 *
	 * @return array<string, mixed>|false
	 */
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
			$parent = $this->uploadParent($uploads['parent']);

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
