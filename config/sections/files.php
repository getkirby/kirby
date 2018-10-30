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
        /**
         * Image options to control the source and look of file previews
         */
        'image' => function ($image = null) {
            return $image ?? [];
        },
        /**
         * Optional info text setup. Info text is shown on the right (lists) or below (cards) the filename.
         */
        'info' => function (string $info = null) {
            return $info;
        },
        /**
         * The size option controls the size of cards. By default cards are auto-sized and the cards grid will always fill the full width. With a size you can disable auto-sizing. Available sizes: tiny, small, medium, large
         */
        'size' => function (string $size = 'auto') {
            return $size;
        },
        /**
         * Enables/disables manual sorting
         */
        'sortable' => function (bool $sortable = true) {
            return $sortable;
        },
        /**
         * Overwrites manual sorting and sorts by the given field and sorting direction (i.e. filename desc)
         */
        'sortBy' => function (string $sortBy = null) {
            return $sortBy;
        },
        /**
         * Filters all files by template and also sets the template, which will be used for all uploads
         */
        'template' => function (string $template = null) {
            return $template;
        },
        /**
         * Setup for the main text in the list or cards. By default this will display the filename.
         */
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
                'attributes' => array_filter([
                    'template' => $this->template
                ])
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
                'size'     => $this->size,
                'sortable' => $this->sortable,
                'upload'   => $this->upload
            ],
            'pagination' => $this->pagination
        ];
    }
];


