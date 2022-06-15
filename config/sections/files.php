<?php

use Kirby\Cms\File;
use Kirby\Toolkit\I18n;

return [
    'mixins' => [
        'details',
        'empty',
        'headline',
        'help',
        'layout',
        'min',
        'max',
        'pagination',
        'parent',
        'search',
        'sort'
    ],
    'props' => [
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
                    'parent'   => $this->model(),
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

            // search
            if ($this->search === true && empty($this->searchterm) === false) {
                $files = $files->search($this->searchterm);
            }

            // sort
            if ($this->sortBy) {
                $files = $files->sort(...$files::sortArgs($this->sortBy));
            } else {
                $files = $files->sorted();
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
                $panel = $file->panel();

                $item = [
                    'dragText'  => $panel->dragText('auto', $dragTextAbsolute),
                    'extension' => $file->extension(),
                    'filename'  => $file->filename(),
                    'id'        => $file->id(),
                    'image'     => $panel->image(
                        $this->image,
                        $this->layout === 'table' ? 'list' : $this->layout
                    ),
                    'info'      => $file->toSafeString($this->info ?? false),
                    'link'      => $panel->url(true),
                    'mime'      => $file->mime(),
                    'parent'    => $file->parent()->panel()->path(),
                    'template'  => $file->template(),
                    'text'      => $file->toSafeString($this->text),
                    'url'       => $file->url(),
                ];

                if ($this->layout === 'table') {
                    $item = $this->columnsValues($item, $file);
                }

                $data[] = $item;
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
        'pagination' => function () {
            return $this->pagination();
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
                    'sort'     => $this->sortable === true ? $total + 1 : null,
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
                'columns'  => $this->columns,
                'empty'    => $this->empty,
                'headline' => $this->headline,
                'help'     => $this->help,
                'layout'   => $this->layout,
                'link'     => $this->link(),
                'max'      => $this->max,
                'min'      => $this->min,
                'search'   => $this->search,
                'size'     => $this->size,
                'sortable' => $this->sortable,
                'upload'   => $this->upload
            ],
            'pagination' => $this->pagination
        ];
    }
];
