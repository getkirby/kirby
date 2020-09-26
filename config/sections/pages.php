<?php

use Kirby\Cms\Blueprint;
use Kirby\Toolkit\A;
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
        'parent'
    ],
    'props' => [
        /**
         * Optional array of templates that should only be allowed to add
         * or `false` to completely disable page creation
         */
        'create' => function ($create = null) {
            return $create;
        },
        /**
         * Enables/disables reverse sorting
         */
        'flip' => function (bool $flip = false) {
            return $flip;
        },
        /**
         * Image options to control the source and look of page previews
         */
        'image' => function ($image = null) {
            return $image ?? [];
        },
        /**
         * Optional info text setup. Info text is shown on the right (lists) or below (cards) the page title.
         */
        'info' => function (string $info = null) {
            return $info;
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
         * Overwrites manual sorting and sorts by the given field and sorting direction (i.e. `date desc`)
         */
        'sortBy' => function (string $sortBy = null) {
            return $sortBy;
        },
        /**
         * Filters pages by their status. Available status settings: `draft`, `unlisted`, `listed`, `published`, `all`.
         */
        'status' => function (string $status = '') {
            if ($status === 'drafts') {
                $status = 'draft';
            }

            if (in_array($status, ['all', 'draft', 'published', 'listed', 'unlisted']) === false) {
                $status = 'all';
            }

            return $status;
        },
        /**
         * Filters the list by templates and sets template options when adding new pages to the section.
         */
        'templates' => function ($templates = null) {
            return A::wrap($templates ?? $this->template);
        },
        /**
         * Setup for the main text in the list or cards. By default this will display the page title.
         */
        'text' => function (string $text = '{{ page.title }}') {
            return $text;
        }
    ],
    'computed' => [
        'parent' => function () {
            return $this->parentModel();
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
                    $pages = $this->parent->childrenAndDrafts();
            }

            // loop for the best performance
            foreach ($pages->data as $id => $page) {

                // remove all protected pages
                if ($page->isReadable() === false) {
                    unset($pages->data[$id]);
                    continue;
                }

                // filter by all set templates
                if ($this->templates && in_array($page->intendedTemplate()->name(), $this->templates) === false) {
                    unset($pages->data[$id]);
                    continue;
                }
            }

            // sort
            if ($this->sortBy) {
                $pages = $pages->sort(...$pages::sortArgs($this->sortBy));
            }

            // flip
            if ($this->flip === true) {
                $pages = $pages->flip();
            }

            // pagination
            $pages = $pages->paginate([
                'page'   => $this->page,
                'limit'  => $this->limit,
                'method' => 'none' // the page is manually provided
            ]);

            return $pages;
        },
        'total' => function () {
            return $this->pages->pagination()->total();
        },
        'data' => function () {
            $data = [];

            foreach ($this->pages as $item) {
                $permissions = $item->permissions();
                $image       = $item->panelImage($this->image);

                $data[] = [
                    'id'          => $item->id(),
                    'dragText'    => $item->dragText(),
                    'text'        => $item->toString($this->text),
                    'info'        => $item->toString($this->info ?? false),
                    'parent'      => $item->parentId(),
                    'icon'        => $item->panelIcon($image),
                    'image'       => $image,
                    'link'        => $item->panelUrl(true),
                    'status'      => $item->status(),
                    'permissions' => [
                        'sort'         => $permissions->can('sort'),
                        'changeSlug'   => $permissions->can('changeSlug'),
                        'changeStatus' => $permissions->can('changeStatus'),
                        'changeTitle'  => $permissions->can('changeTitle')
                    ]
                ];
            }

            return $data;
        },
        'errors' => function () {
            $errors = [];

            if ($this->validateMax() === false) {
                $errors['max'] = I18n::template('error.section.pages.max.' . I18n::form($this->max), [
                    'max'     => $this->max,
                    'section' => $this->headline
                ]);
            }

            if ($this->validateMin() === false) {
                $errors['min'] = I18n::template('error.section.pages.min.' . I18n::form($this->min), [
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
        'add' => function () {
            if ($this->create === false) {
                return false;
            }

            if (in_array($this->status, ['draft', 'all']) === false) {
                return false;
            }

            if ($this->isFull() === true) {
                return false;
            }

            return true;
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
            if (in_array($this->status, ['listed', 'published', 'all']) === false) {
                return false;
            }

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
        }
    ],
    'methods' => [
        'blueprints' => function () {
            $blueprints = [];
            $templates  = empty($this->create) === false ? A::wrap($this->create) : $this->templates;

            if (empty($templates) === true) {
                $templates = $this->kirby()->blueprints();
            }

            // convert every template to a usable option array
            // for the template select box
            foreach ($templates as $template) {
                try {
                    $props = Blueprint::load('pages/' . $template);

                    $blueprints[] = [
                        'name'  => basename($props['name']),
                        'title' => $props['title'],
                    ];
                } catch (Throwable $e) {
                    $blueprints[] = [
                        'name'  => basename($template),
                        'title' => ucfirst($template),
                    ];
                }
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
                'empty'    => $this->empty,
                'headline' => $this->headline,
                'help'     => $this->help,
                'layout'   => $this->layout,
                'link'     => $this->link,
                'max'      => $this->max,
                'min'      => $this->min,
                'size'     => $this->size,
                'sortable' => $this->sortable
            ],
            'pagination' => $this->pagination,
        ];
    }
];
