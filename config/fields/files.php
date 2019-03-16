<?php

use Kirby\Toolkit\A;

return [
    'mixins' => ['min'],
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
         * Changes the layout of the selected files. Available layouts: list, cards
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
         * Query for the files to be included
         */
        'query' => function (string $query = null) {
            return $query;
        },

        /**
         * Layout size for cards
         */
        'size' => function (string $size = null) {
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
            if ($this->layout === 'list') {
                $thumb = [
                    'width'  => 100,
                    'height' => 100
                ];
            } else {
                $thumb = [
                    'width'  => 400,
                    'height' => 400
                ];
            }

            $image = $file->panelImage($this->image, $thumb);
            $model = $this->model();
            $uuid  = $file->parent() === $model ? $file->filename() : $file->id();

            return [
                'filename' => $file->filename(),
                'text'     => $file->toString($this->text),
                'link'     => $file->panelUrl(true),
                'id'       => $file->id(),
                'uuid'     => $uuid,
                'url'      => $file->url(),
                'info'     => $file->toString($this->info ?? false),
                'image'    => $image,
                'icon'     => $file->panelIcon($image),
                'type'     => $file->type(),
            ];
        },
        'toFiles' => function ($value = null) {
            $files = [];
            $kirby = kirby();

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
                    $files = $field->model()->query($field->query(), 'Kirby\Cms\Files');
                    $data  = [];

                    foreach ($files as $index => $file) {
                        $data[] = $field->fileResponse($file);
                    }

                    return $data;
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
