<?php

use Kirby\Cms\Api;
use Kirby\Cms\File;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;

return [
	'props' => [
		/**
		 * Sets the upload options for linked files (since 3.2.0)
		 */
		'uploads' => function ($uploads = []) {
			if ($uploads === false) {
				return false;
			}

			if (is_string($uploads) === true) {
				$uploads = ['template' => $uploads];
			}

			if (is_array($uploads) === false) {
				$uploads = [];
			}

			$uploads['accept']  = '*';

			if ($preview = $this->image) {
				$uploads['preview'] = $preview;
			}

			if ($template = $uploads['template'] ?? null) {
				// get parent object for upload target
				$parent = $this->uploadParent($uploads['parent'] ?? null);

				if ($parent === null) {
					throw new InvalidArgumentException('"' . $uploads['parent'] . '" could not be resolved as a valid parent for the upload');
				}

				$file = new File([
					'filename' => 'tmp',
					'parent'   => $parent,
					'template' => $template
				]);

				$uploads['accept'] = $file->blueprint()->acceptAttribute();
			}

			return $uploads;
		},
	],
	'methods' => [
		'upload' => function (Api $api, $params, Closure $map) {
			if ($params === false) {
				throw new Exception('Uploads are disabled for this field');
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
					throw new Exception('The file could not be uploaded');
				}

				return $map($file, $parent);
			});
		},
		'uploadParent' => function (string|null $parentQuery = null) {
			$parent = $this->model();

			if ($parentQuery) {
				$parent = $parent->query($parentQuery);
			}

			if ($parent instanceof File) {
				$parent = $parent->parent();
			}

			return $parent;
		}
	]
];
