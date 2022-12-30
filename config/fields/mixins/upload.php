<?php

use Kirby\Cms\Api;
use Kirby\Cms\File;
use Kirby\Exception\Exception;

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

			$template = $uploads['template'] ?? null;

			if ($template) {
				$file = new File([
					'filename' => 'tmp',
					'parent'   => $this->model(),
					'template' => $template
				]);

				$uploads['accept'] = $file->blueprint()->acceptMime();
			} else {
				$uploads['accept'] = '*';
			}

			return $uploads;
		},
	],
	'methods' => [
		'upload' => function (Api $api, $params, Closure $map) {
			if ($params === false) {
				throw new Exception('Uploads are disabled for this field');
			}

			if ($parentQuery = ($params['parent'] ?? null)) {
				$parent = $this->model()->query($parentQuery);
			} else {
				$parent = $this->model();
			}

			if ($parent instanceof File) {
				$parent = $parent->parent();
			}

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
		}
	]
];
