<?php

use Kirby\Toolkit\A;

return [
    'props' => [
        'default' => function ($default = null) {
            return $this->toFiles($default);
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

            if (is_string($parent) === true && $model = $this->model()->query($parent, 'Kirby\Cms\Model')) {
                return $model->apiUrl(true);
            }

            return $this->model()->apiUrl(true);

        },
        'value' => function ($value = null) {
            return $this->toFiles($value);
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

                if ($id !== null && ($file = $kirby->file($id, $this->model, true))) {
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
