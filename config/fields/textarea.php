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
                    $files = $field->model()->query($field->files['query'] ?? 'page.files', 'Kirby\Cms\Files');
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
                    $field = $this->field();

                    return $this->upload(function ($source, $filename) use ($field) {
                        $file = $field->model()->createFile([
                            'source'   => $source,
                            'filename' => $filename
                        ]);

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
