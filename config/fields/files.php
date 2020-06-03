<?php

use Kirby\Toolkit\Str;

return [
    'mixins' => [
        'filepicker',
        'min',
        'picker',
        'preview',
        'upload'
    ],
    'computed' => [
        'parentModel' => function () {
            if (
                is_string($this->parent) === true &&
                $model = $this->model()->query($this->parent, 'Kirby\Cms\Model')
            ) {
                return $model;
            }

            return $this->model();
        },
        'parent' => function () {
            return $this->parentModel->apiUrl(true);
        },
        'query' => function () {
            return $this->query ?? $this->parentModel::CLASS_ALIAS . '.files';
        }
    ],
    'methods' => [
        'toModel' => function ($id = null) {
            return $this->kirby()->file($id, $this->model());
        }
    ],
    'api' => function () {
        return [
            [
                'pattern' => 'items',
                'action'  => function () {
                    $field = $this->field();
                    $ids   = Str::split($this->requestQuery('ids'));
                    return $field->toModels($ids);
                }
            ],
            [
                'pattern' => 'options',
                'action'  => function () {
                    $field = $this->field();

                    return $field->filepicker([
                        'info'    => $field->info(),
                        'limit'   => $field->limit(),
                        'page'    => $this->requestQuery('page'),
                        'preview' => $field->preview(),
                        'query'   => $field->query(),
                        'search'  => $this->requestQuery('search'),
                        'text'    => $field->text()
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
                            'info'    => $field->info(),
                            'model'   => $field->model(),
                            'preview' => $field->preview(),
                            'text'    => $field->text(),
                        ]);
                    });
                }
            ]
        ];
    }
];
