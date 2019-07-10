<?php

use Kirby\Cms\Api;
use Kirby\Cms\File;

return [
    'props' => [
        /**
         * Sets the upload options for linked files
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
                    'template' => $template
                ]);

                $uploads['accept'] = $file->blueprint()->accept()['mime'] ?? '*';
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

            if (is_a($parent, 'Kirby\Cms\File') === true) {
                $parent = $parent->parent();
            }

            return $api->upload(function ($source, $filename) use ($parent, $params, $map) {
                $file = $parent->createFile([
                    'source'   => $source,
                    'template' => $params['template'] ?? null,
                    'filename' => $filename,
                ]);

                if (is_a($file, 'Kirby\Cms\File') === false) {
                    throw new Exception('The file could not be uploaded');
                }

                return $map($file);
            });
        }
    ]
];
