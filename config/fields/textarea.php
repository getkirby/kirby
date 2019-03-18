<?php

return [
    'props' => [
        /**
         * Unset inherited props
         */
        'after'  => null,
        'before' => null,

        /**
         * Enables/disables the format buttons. Can either be true/false or a list of allowed buttons. Available buttons: headlines, italic, bold, link, email, list, code, ul, ol
         */
        'buttons' => function ($buttons = true) {
            return $buttons;
        },

        /**
         * Enables/disables the character counter in the top right corner
         */
        'counter' => function (bool $counter = true) {
            return $counter;
        },

        /**
         * Sets the default text when a new Page/File/User is created
         */
        'default' => function (string $default = null) {
            return trim($default);
        },

        /**
         * Sets the options for the files picker
         */
        'files' => function ($files = []) {
            if (is_string($files) === true) {
                return ['query' => $files];
            }

            if (is_array($files) === false) {
                $files = [];
            }

            return $files;
        },

        /**
         * Maximum number of allowed characters
         */
        'maxlength' => function (int $maxlength = null) {
            return $maxlength;
        },

        /**
         * Minimum number of required characters
         */
        'minlength' => function (int $minlength = null) {
            return $minlength;
        },

        /**
         * Changes the size of the textarea. Available sizes: small, medium, large, huge
         */
        'size' => function (string $size = null) {
            return $size;
        },

        /**
         * Sets the upload options for linked files
         */
        'uploads' => function ($uploads = []) {
            if ($uploads === false) {
                return false;
            }

            if (is_string($uploads) === true) {
                return ['template' => $uploads];
            }

            if (is_array($uploads) === false) {
                $uploads = [];
            }

            return $uploads;
        },

        'value' => function (string $value = null) {
            return trim($value);
        }
    ],
    'api' => function () {
        return [
            [
                'pattern' => 'files',
                'action' => function () {
                    $field = $this->field();
                    $model = $field->model();

                    if (empty($filed->files['query']) === false) {
                        $query = $filed->files['query'];
                    } elseif (is_a($model, 'Kirby\Cms\File') === true) {
                        $query = 'file.siblings';
                    } else {
                        $query = $model::CLASS_ALIAS . '.files';
                    }

                    $files = $model->query($query, 'Kirby\Cms\Files');
                    $data  = [];

                    foreach ($files as $index => $file) {
                        $image = $file->panelImage($field->files['image'] ?? []);
                        $model = $field->model();

                        $data[] = [
                            'filename' => $file->filename(),
                            'dragText' => $file->dragText(),
                            'image'    => $image,
                            'icon'     => $file->panelIcon($image)
                        ];
                    }

                    return $data;
                }
            ],
            [
                'pattern' => 'upload',
                'action' => function () {
                    $field   = $this->field();
                    $uploads = $field->uploads();

                    if ($uploads === false) {
                        throw new Exception('Uploads are disabled for this field');
                    }

                    if ($parentQuery = ($uploads['parent'] ?? null)) {
                        $parent = $field->model()->query($parentQuery);
                    } else {
                        $parent = $field->model();
                    }

                    if (is_a($parent, 'Kirby\Cms\File') === true) {
                        $parent = $parent->parent();
                    }

                    return $this->upload(function ($source, $filename) use ($field, $parent, $uploads) {
                        $file = $parent->createFile([
                            'source'   => $source,
                            'template' => $uploads['template'] ?? null,
                            'filename' => $filename,
                        ]);

                        if (is_a($file, 'Kirby\Cms\File') === false) {
                            throw new Exception('The file could not be uploaded');
                        }

                        return [
                            'filename' => $file->filename(),
                            'dragText' => $file->dragText(),
                        ];
                    });
                }
            ]
        ];
    },
    'validations' => [
        'minlength',
        'maxlength'
    ]
];
