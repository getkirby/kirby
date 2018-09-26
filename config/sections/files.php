<?php

use Kirby\Cms\File;
use Kirby\Toolkit\Str;

return [
    'mixins' => [
        'headline',
        'layout',
        'min',
        'max',
        'pagination',
        'parent',
    ],
    'props' => [
        'image' => function ($image = null) {
            return $image ?? [];
        },
        'info' => function (string $info = null) {
            return $info;
        },
        'sortable' => function (bool $sortable = true) {
            return $sortable;
        },
        'sortBy' => function (string $sortBy = null) {
            return $sortBy;
        },
        'template' => function (string $template = null) {
            return $template;
        },
        'text' => function (string $text = '{{ file.filename }}') {
            return $text;
        }
    ],
    'computed' => [
        'accept' => function () {
            if ($this->template) {
                $file = new File([
                    'filename' => 'tmp',
                    'template' => $this->template
                ]);

                return $file->blueprint()->accept()['mime'] ?? '*';
            }

            return null;
        },
        'dragTextType' => function () {
            return (option('panel')['kirbytext'] ?? true) ? 'kirbytext' : 'markdown';
        },
        'parent' => function () {
            return $this->parent();
        },
        'files' => function () {
            $files = $this->parent->files()->template($this->template);

            if ($this->sortBy) {
                $files = $files->sortBy(...Str::split($this->sortBy, ' '));
            } elseif ($this->sortable === true) {
                $files = $files->sortBy('sort', 'asc');
            }

            // apply the default pagination
            $files = $files->paginate([
                'page'  => $this->page,
                'limit' => $this->limit
            ]);

            return $files;
        },
        'data' => function () {

            $data = [];

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

            foreach ($this->files as $file) {
                $data[] = [
                    'dragText' => $file->dragText($this->dragTextType),
                    'filename' => $file->filename(),
                    'id'       => $file->id(),
                    'text'     => $file->toString($this->text),
                    'info'     => $file->toString($this->info ?? false),
                    'icon'     => $file->panelIcon(),
                    'image'    => $file->panelImage($this->image, $thumb),
                    'link'     => $file->panelUrl(true),
                    'parent'   => $file->parentId(),
                    'url'      => $file->url(),
                ];
            }

            return $data;
        },
        'total' => function () {
            return $this->files->pagination()->total();
        },
        'errors' => function () {

            $errors = [];

            if ($this->validateMax() === false) {
                $errors['max'] = Str::template(
                    I18n::translate('error.files.max.' . r($this->max === 1, 'singular', 'plural')),
                    [
                        'max'     => $this->max,
                        'section' => $this->headline
                    ]
                );
            }

            if ($this->validateMin() === false) {
                $errors['min'] = Str::template(
                    I18n::translate('error.files.min.' .  r($this->min === 1, 'singular', 'plural')),
                    [
                        'min'     => $this->min,
                        'section' => $this->headline
                    ]
                );
            }

            if (empty($errors) === true) {
                return [];
            }

            return [
                $this->name => [
                    'label'   => $this->headline,
                    'message' => $errors,
                ]
            ];

        },
        'link' => function () {

            $modelLink  = $this->model->panelUrl(true);
            $parentLink = $this->parent->panelUrl(true);

            if ($modelLink !== $parentLink) {
                return $parentLink;
            }

        },
        'pagination' => function () {
            return $this->pagination();
        },
        'sortable' => function () {

            if ($this->sortable === false) {
                return false;
            }

            if ($this->sortBy !== null) {
                return false;
            }

            return true;

        },
        'upload' => function () {

            if ($this->isFull() === true) {
                return false;
            }

            if ($this->max && count($this->data) === $this->max - 1) {
                $multiple = false;
            } else {
                $multiple = true;
            }

            return [
                'accept'     => $this->accept,
                'multiple'   => $multiple,
                'api'        => $this->parent->apiUrl(true) . '/files',
                'attributes' => [
                    'template' => $this->template
                ]
            ];

        }
    ],
    'toArray' => function () {
        return [
            'data'    => $this->data,
            'errors'  => $this->errors,
            'options' => [
                'accept'   => $this->accept,
                'headline' => $this->headline,
                'layout'   => $this->layout,
                'link'     => $this->link,
                'max'      => $this->max,
                'sortable' => $this->sortable,
                'upload'   => $this->upload
            ],
            'pagination' => $this->pagination
        ];
    }
];


