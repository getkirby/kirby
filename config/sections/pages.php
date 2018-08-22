<?php

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Toolkit\F;

return [
    'mixins' => [
        'headline',
        'layout',
        'min',
        'max',
        'pagination',
        'parent'
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
        'status' => function (string $status = '') {

            if ($status === 'drafts') {
                $status = 'draft';
            }

            if (in_array($status, ['all', 'draft', 'published', 'listed', 'unlisted']) === false) {
                $status = 'all';
            }

            return $status;

        },
        'templates' => function ($templates = null) {

            if (is_string($templates) === true) {
                $templates = [$templates];
            }

            if ($templates === null) {
                $templates = [];
            }

            if (is_array($templates) === false) {
                $templates = [];
            }

            return $templates;

        },
        'text' => function (string $text = '{{ page.title }}') {
            return $text;
        }
    ],
    'computed' => [
        'dragTextType' => function () {
            return (option('panel')['kirbytext'] ?? true) ? 'kirbytext' : 'markdown';
        },
        'parent' => function () {
            return $this->parent();
        },
        'pages' => function () {

            switch ($this->status) {
                case 'draft':
                    $pages = $this->parent->drafts();
                    break;
                case 'listed':
                    $pages = $this->parent->children()->listed();
                    break;
                case 'published':
                    $pages = $this->parent->children();
                    break;
                case 'unlisted':
                    $pages = $this->parent->children()->unlisted();
                    break;
                default:
                    $pages = $this->parent->children()->merge('drafts');
            }

            // filter by all set templates
            if ($this->templates) {
                $pages = $pages->template($this->templates);
            }

            // sort
            if ($this->sortBy) {
                $pages = $pages->sortBy(...Str::split($this->sortBy, ' '));
            }

            // pagination
            $pages = $pages->paginate([
                'page'  => $this->page,
                'limit' => $this->limit
            ]);

            return $pages;
        },
        'data' => function () {

            $data = [];

            foreach ($this->pages as $item) {

                $permissions = $item->permissions();
                $blueprint   = $item->blueprint();

                $data[] = [
                    'id'          => $item->id(),
                    'dragText'    => $item->dragText($this->dragTextType),
                    'text'        => $item->toString($this->text),
                    'info'        => $item->toString($this->info ?? false),
                    'parent'      => $item->parentId(),
                    'icon'        => $item->panelIcon(),
                    'image'       => $item->panelImage($this->image),
                    'link'        => $item->panelUrl(true),
                    'url'         => $item->url(),
                    'status'      => $item->status(),
                    'permissions' => [
                        'sort'         => $permissions->can('sort'),
                        'changeStatus' => $permissions->can('changeStatus')
                    ]
                ];

            }

            return $data;

        },
        'errors' => function () {

            $errors = [];

            if ($this->validateMax() === false) {
                $errors['max'] = 'You must not add more than ' . $this->max . ' page(s) to the section "' . $this->headline . '"';
            }

            if ($this->validateMin() === false) {
                $errors['min'] = 'You must at least add ' . $this->min . ' page(s) to the section "' . $this->headline . '"';
            }

            return $errors;

        },
        'add' => function () {

            if (in_array($this->status, ['draft', 'all']) === false) {
                return false;
            }

            if ($this->isFull() === true) {
                return false;
            }

            return count($this->templates) > 0;

        },
        'link' => function () {

            $modelLink  = $this->model->panelUrl(true);
            $parentLink = $this->parent->panelUrl(true);

            if ($modelLink !== $parentLink) {
                return $parentLink;
            }

        },
        'total' => function () {
            return $this->pages->pagination()->total();
        },
        'pagination' => function () {
            return $this->pagination();
        },
        'sortable' => function () {

            if ($this->status !== 'listed' && $this->status !== 'all') {
                return false;
            }

            if ($this->sortable === false) {
                return false;
            }

            if ($this->sortBy !== null) {
                return false;
            }

            return true;

        }
    ],
    'methods' => [
        'blueprints' => function () {

            $blueprints = [];
            $templates  = $this->templates;

            if (empty($templates) === true) {
                foreach (glob(App::instance()->root('blueprints') . '/pages/*.yml') as $blueprint) {
                    $templates[] = F::name($blueprint);
                }
            }

            // convert every template to a usable option array
            // for the template select box
            foreach ($templates as $template) {
                $props = Blueprint::load('pages/' . $template);

                $blueprints[] = [
                    'name'  => basename($props['name']),
                    'title' => $props['title'],
                ];
            }

            return $blueprints;

        }
    ],
    'toArray' => function () {
        return [
            'data'    => $this->data,
            'errors'  => $this->errors,
            'options' => [
                'add'      => $this->add,
                'headline' => $this->headline,
                'layout'   => $this->layout,
                'link'     => $this->link,
                'sortable' => $this->sortable
            ],
            'pagination' => $this->pagination,
        ];
    }
];


