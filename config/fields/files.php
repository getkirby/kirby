<?php

use Kirby\Toolkit\A;

return [
    'props' => [
        'default' => function ($default = null) {
            return $default;
        },
        'layout' => function (string $layout = 'list') {
            return $layout;
        },
        'min' => function (int $min = null) {
            return $min;
        },
        'max' => function (int $max = null) {
            return $max;
        },
        'multiple' => function (bool $multiple = true) {
            return $multiple;
        },
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
