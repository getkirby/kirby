<?php

use Kirby\Data\Yaml;
use Kirby\Toolkit\A;

return [
    'mixins' => [
        'picker',
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
         * Changes the layout of the selected files. Available layouts: `list`, `cards`
         */
        'layout' => function (string $layout = 'list') {
            return $layout;
        },

        /**
         * Layout size for cards: `tiny`, `small`, `medium`, `large` or `huge`
         */
        'size' => function (string $size = 'auto') {
            return $size;
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
            $response = $file->panelPickerData([
                'image' => $this->image,
                'info'  => $this->info ?? false,
                'model' => $this->model(),
                'text'  => $this->text,
            ]);

            if ($this->primaryKey !== null && empty($response[$this->primaryKey]) === true) {
                $response[$this->primaryKey] = $file->content()->get($this->primaryKey)->value();
            }

            return $response;
        },
        'toFiles' => function ($value = null) {
            $files = [];

            foreach (Yaml::decode($value) as $data) {
                $id = is_array($data) === true ? ($data['id'] ?? null) : $data;

                if (empty($id) === false) {
                    if ($this->primaryKey === null || is_array($data) === true) {
                        $file = $this->kirby()->file($id, $this->model());
                    } else {
                        $parent = $this->model() ?? $this->site();
                        $file = $parent->files()->findBy($this->primaryKey, $id);
                    }

                    if ($file) {
                        $files[] = $this->fileResponse($file);
                    }
                }
            }

            return $files;
        }
    ],
    'api' => function () {
        return [
            [
                'pattern' => '/',
                'action'  => function () {
                    $field = $this->field();

                    return $field->filepicker([
                        'image'  => $field->image(),
                        'info'   => $field->info(),
                        'limit'  => $field->limit(),
                        'page'   => $this->requestQuery('page'),
                        'query'  => $field->query(),
                        'search' => $this->requestQuery('search'),
                        'text'   => $field->text()
                    ]);
                }
            ],
            [
                'pattern' => 'upload',
                'method'  => 'POST',
                'action'  => function () {
                    $field   = $this->field();
                    $uploads = $field->uploads();

                    return $field->upload($this, $uploads, function ($file, $parent) use ($field) {
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
        return A::pluck($value, $this->primaryKey ?? 'uuid');
    },
    'validations' => [
        'max',
        'min'
    ]
];
