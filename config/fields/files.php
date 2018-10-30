<?php

use Kirby\Toolkit\A;

return [
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
         * Optional query for the parent page that will be used to fetch the files. (i.e site.find("media"))
         */
        'parent' => function (string $parent = null) {
            return $parent;
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
        'default' => function () {
            return $this->toFiles($this->default);
        },
        'value' => function () {
            return $this->toFiles($this->value);
        },
    ],
    'methods' => [
        'toFiles' => function ($value = null) {

            $files = [];
            $kirby = kirby();

            foreach (Yaml::decode($value) as $id) {

                if (is_array($id) === true) {
                    $id = $id['filename'] ?? null;
                }

                if ($id !== null && ($file = $this->parentModel->file($id))) {
                    $files[] = [
                        'filename' => $file->filename(),
                        'link'     => $file->panelUrl(true),
                        'id'       => $file->id(),
                        'url'      => $file->url(),
                        'thumb'    => $file->isResizable() ? $file->resize(512)->url() : null,
                        'type'     => $file->type(),
                    ];
                }
            }

            return $files;

        }
    ],
    'save' => function ($value = null) {
        return A::pluck($value, 'filename');
    },
    'validations' => [
        'max',
        'min'
    ]
];
