<?php

return [
    'props' => [
        'layout' => function (string $value = 'list') {
            return $value;
        },
        'max' => function (int $max = null) {
            return $max;
        },
        'multiple' => function (bool $multiple = true) {
            return $multiple;
        },
        'parent' => function () {
            return $this->model()->panelUrl(true);
        },
        'value' => function ($value = null) {

            $files = [];
            $kirby = kirby();

            foreach (Yaml::decode($value) as $id) {

                if (is_array($id) === true) {
                    $id = $id['id'] ?? null;
                }

                if ($id !== null && ($file = $kirby->file($id))) {
                    $files[] = [
                        'filename' => $file->filename(),
                        'link'     => $file->panelUrl(true),
                        'id'       => $file->id(),
                        'url'      => $file->url()
                    ];
                }
            }

            return $files;

        },
    ],
    'methods' => [
        'toString' => function ($value = null) {
            if (is_array($value) === true) {
                return Yaml::encode(array_column($value, 'id'));
            }

            return '';
        }
    ]
];
