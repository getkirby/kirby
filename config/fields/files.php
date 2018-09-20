<?php

return [
    'props' => [
        'default' => function ($default = null) {
            return $this->toFiles($default);
        },
        'layout' => function (string $layout = 'list') {
            return $layout;
        },
        'max' => function (int $max = null) {
            return $max;
        },
        'multiple' => function (bool $multiple = true) {
            return $multiple;
        },
        'parent' => function (string $parent = null) {

            if ($parent === null) {
                return $this->model()->apiUrl(true);
            }

            return $this->model()->query($parent, 'Kirby\Cms\Model')->apiUrl(true);

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

        }
    ],
    'toString' => function ($value = null) {
        if (is_array($value) === true) {
            return Yaml::encode(array_column($value, 'id'));
        }

        return '';
    }
];
