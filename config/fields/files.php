<?php

use Kirby\Toolkit\A;

return [
    'mixins' => [
        'filepicker',
        'min',
        'upload'
    ],
    'props' => [
        /**
         * Unset inherited props
         */
        'after'       => null,
        'before'      => null,
        'autofocus'   => null,
        'icon'        => null,
        'placeholder' => null,

        /**
         * Sets the file(s), which are selected by default when a new page is created
         */
        'default' => function ($default = null) {
            return $default;
        },

        /**
         * The placeholder text if no pages have been selected yet
         */
        'empty' => function ($empty = null) {
            return I18n::translate($empty, $empty);
        },

        /**
         * Image settings for each item
         */
        'image' => function (array $image = null) {
            return $image ?? [];
        },

        /**
         * Info text
         */
        'info' => function (string $info = null) {
            return $info;
        },

        /**
         * Changes the layout of the selected files. Available layouts: `list`, `cards`
         */
        'layout' => function (string $layout = 'list') {
            return $layout;
        },

        /**
         * Minimum number of required files
         */
        'min' => function (int $min = null) {
            return $min;
        },

        /**
         * Maximum number of allowed files
         */
        'max' => function (int $max = null) {
            return $max;
        },

        /**
         * If false, only a single file can be selected
         */
        'multiple' => function (bool $multiple = true) {
            return $multiple;
        },

        /**
         * Query for the files to be included in the picker
         */
        'query' => function (string $query = null) {
            return $query;
        },

        /**
         * Layout size for cards: `tiny`, `small`, `medium`, `large` or `huge`
         */
        'size' => function (string $size = 'auto') {
            return $size;
        },

        /**
         * Main text
         */
        'text' => function (string $text = '{{ file.filename }}') {
            return $text;
        },

        'value' => function ($value = null) {
            return $value;
        }
    ],
    'computed' => [
        'parentModel' => function () {
            if (is_string($this->parent) === true && $model = $this->model()->query($this->parent, 'Kirby\Cms\Model')) {
                return $model;
            }

            return $this->model();
        },
        'parent' => function () {
            return $this->parentModel->apiUrl(true);
        },
        'query' => function () {
            return $this->query ?? $this->parentModel::CLASS_ALIAS . '.files';
        },
        'default' => function () {
            return $this->toFiles($this->default);
        },
        'value' => function () {
            return $this->toFiles($this->value);
        },
    ],
    'methods' => [
        'fileResponse' => function ($file) {
            return $file->panelPickerData([
                'image' => $this->image,
                'info'  => $this->info ?? false,
                'model' => $this->model(),
                'text'  => $this->text,
            ]);
        },
        'toFiles' => function ($value = null) {
            $files = [];

            foreach (Yaml::decode($value) as $id) {
                if (is_array($id) === true) {
                    $id = $id['id'] ?? null;
                }

                if ($id !== null && ($file = $this->kirby()->file($id, $this->model()))) {
                    $files[] = $this->fileResponse($file);
                }
            }

            return $files;
        }
    ],
    'api' => function () {
        return [
            [
                'pattern' => '/',
                'action' => function () {
                    $field = $this->field();

                    return $field->filepicker([
                        'query' => $field->query(),
                        'image' => $field->image(),
                        'info'  => $field->info(),
                        'text'  => $field->text()
                    ]);
                }
            ],
            [
                'pattern' => 'upload',
                'action' => function () {
                    $field   = $this->field();
                    $uploads = $field->uploads();

                    return $field->upload($this, $uploads, function ($file) use ($field) {
                        return $file->panelPickerData([
                            'image' => $field->image(),
                            'info'  => $field->info(),
                            'model' => $field->model(),
                            'text'  => $field->text(),
                        ]);
                    });
                }
            ]
        ];
    },
    'save' => function ($value = null) {
        return A::pluck($value, 'uuid');
    },
    'validations' => [
        'max',
        'min'
    ]
];
