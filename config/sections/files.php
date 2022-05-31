<?php

use Kirby\Cms\File;
use Kirby\Toolkit\I18n;

return [
    'mixins' => [
        'columns',
        'details',
        'empty',
        'headline',
        'help',
        'layout',
        'limits',
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
        'models' => function () {
            $files = $this->parent->files()->template($this->template);

            // filter out all protected files
            $files = $files->filter('isReadable', true);

            // search
            if ($this->search === true && empty($this->query) === false) {
                $files = $files->search($this->query);
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

            foreach ($this->models as $model) {
                $panel = $model->panel();

                $item = [
                    'dragText'  => $panel->dragText('auto', $dragTextAbsolute),
                    'extension' => $model->extension(),
                    'filename'  => $model->filename(),
                    'id'        => $model->id(),
                    'image'     => $panel->image(
                        $this->image,
                        $this->layout === 'table' ? 'list' : $this->layout
                    ),
                    'info'      => $model->toSafeString($this->info ?? false),
                    'link'      => $panel->url(true),
                    'mime'      => $model->mime(),
                    'parent'    => $model->parent()->panel()->path(),
                    'template'  => $model->template(),
                    'text'      => $model->toSafeString($this->text),
                    'url'       => $model->url(),
                ];

                if ($this->layout === 'table') {
                    $item = $this->columnsValues($item, $model);
                }

                $data[] = $item;
            }

            return $data;
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
                'link'     => $this->link,
                'max'      => $this->max,
                'min'      => $this->min,
                'query'    => $this->query,
                'search'   => $this->search,
                'size'     => $this->size,
                'sortable' => $this->sortable,
                'upload'   => $this->upload
            ],
            'pagination' => $this->pagination
        ];
    }
];
