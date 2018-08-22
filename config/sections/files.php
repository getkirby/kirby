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

            foreach ($this->files as $file) {
                $data[] = [
                    'dragText' => $file->dragText($this->dragTextType),
                    'filename' => $file->filename(),
                    'id'       => $file->id(),
                    'text'     => $file->toString($this->text),
                    'info'     => $file->toString($this->info ?? false),
                    'icon'     => $file->panelIcon(),
                    'image'    => $file->panelImage($this->image),
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
                $errors['max'] = 'You must not add more than ' . $this->max . ' file(s) to the section "' . $this->headline . '"';
            }

            if ($this->validateMin() === false) {
                $errors['min'] = 'You must at least add ' . $this->min . ' file(s) to the section "' . $this->headline . '"';
            }

            return $errors;

        },
        'add' => function () {
            return $this->isFull() === false;
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

        }
    ],
    'toArray' => function () {
        return [
            'data'    => $this->data,
            'errors'  => $this->errors,
            'options' => [
                'accept'   => $this->accept,
                'add'      => $this->add,
                'headline' => $this->headline,
                'layout'   => $this->layout,
                'link'     => $this->link,
                'max'      => $this->max,
                'sortable' => $this->sortable,
                'template' => $this->template,
            ],
            'pagination' => $this->pagination
        ];
    }
];


