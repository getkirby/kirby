<?php

use Kirby\Cms\File;
use Kirby\Toolkit\I18n;

return [
    'mixins' => [
        'empty',
        'headline',
        'help',
        'layout',
        'min',
        'max',
        'pagination',
        'parent',
    ],
    'props' => [
        /**
         * Enables/disables reverse sorting
         */
        'flip' => function (bool $flip = false) {
            return $flip;
        },
        /**
         * Image options to control the source and look of file previews
         */
        'image' => function ($image = null) {
            return $image ?? [];
        },
        /**
         * Optional info text setup. Info text is shown on the right (lists) or below (cards) the filename.
         */
        'info' => function ($info = null) {
            return I18n::translate($info, $info);
        },
        /**
         * The size option controls the size of cards. By default cards are auto-sized and the cards grid will always fill the full width. With a size you can disable auto-sizing. Available sizes: `tiny`, `small`, `medium`, `large`, `huge`
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
         * Overwrites manual sorting and sorts by the given field and sorting direction (i.e. `filename desc`)
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
        'text' => function ($text = '{{ file.filename }}') {
            return I18n::translate($text, $text);
        }
    ],
    'computed' => [
        'accept' => function () {
            if ($this->template) {
                $file = new File([
                    'filename' => 'tmp',
                    'template' => $this->template
                ]);

                return $file->blueprint()->acceptMime();
            }

            return null;
        },
        'parent' => function () {
            return $this->parentModel();
        },
        'files' => function () {
            $files = $this->parent->files()->template($this->template);

            // filter out all protected files
            $files = $files->filter('isReadable', true);

            if ($this->sortBy) {
                $files = $files->sort(...$files::sortArgs($this->sortBy));
            } else {
                $files = $files->sort('sort', 'asc', 'filename', 'asc');
            }

            // flip
            if ($this->flip === true) {
                $files = $files->flip();
            }

            // apply the default pagination
            $files = $files->paginate([
                'page'   => $this->page,
                'limit'  => $this->limit,
                'method' => 'none' // the page is manually provided
            ]);

            return $files;
        },
        'data' => function () {
            $data = [];

            // the drag text needs to be absolute when the files come from
            // a different parent model
            $dragTextAbsolute = $this->model->is($this->parent) === false;

            foreach ($this->files as $file) {
                $image = $file->panelImage($this->image);

                $data[] = [
                    'dragText' => $file->dragText('auto', $dragTextAbsolute),
                    'extension' => $file->extension(),
                    'filename' => $file->filename(),
                    'id'       => $file->id(),
                    'icon'     => $file->panelIcon($image),
                    'image'    => $image,
                    'info'     => $file->toString($this->info ?? false),
                    'link'     => $file->panelUrl(true),
                    'mime'     => $file->mime(),
                    'parent'   => $file->parent()->panelPath(),
                    'text'     => $file->toString($this->text),
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
                $errors['max'] = I18n::template('error.section.files.max.' . I18n::form($this->max), [
                    'max'     => $this->max,
                    'section' => $this->headline
                ]);
            }

            if ($this->validateMin() === false) {
                $errors['min'] = I18n::template('error.section.files.min.' . I18n::form($this->min), [
                    'min'     => $this->min,
                    'section' => $this->headline
                ]);
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

            if ($this->flip === true) {
                return false;
            }

            return true;
        },
        'upload' => function () {
            if ($this->isFull() === true) {
                return false;
            }

            // count all uploaded files
            $total = count($this->data);
            $max   = $this->max ? $this->max - $total : null;

            if ($this->max && $total === $this->max - 1) {
                $multiple = false;
            } else {
                $multiple = true;
            }

            $template = $this->template === 'default' ? null : $this->template;

            return [
                'accept'     => $this->accept,
                'multiple'   => $multiple,
                'max'        => $max,
                'api'        => $this->parent->apiUrl(true) . '/files',
                'attributes' => array_filter([
                    'template' => $template
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
                'apiUrl'   => $this->parent->apiUrl(true),
                'empty'    => $this->empty,
                'headline' => $this->headline,
                'help'     => $this->help,
                'layout'   => $this->layout,
                'link'     => $this->link,
                'max'      => $this->max,
                'min'      => $this->min,
                'size'     => $this->size,
                'sortable' => $this->sortable,
                'upload'   => $this->upload
            ],
            'pagination' => $this->pagination
        ];
    }
];
