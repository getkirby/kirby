<?php

use Kirby\Cms\Blueprint;
use Kirby\Exception\InvalidArgumentException;
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
        'parent',
        'search'
    ],
    'props' => [
        'columns' => function (array $columns = null) {
            return $columns ?? [];
        },
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
        'text' => function ($text = '{{ page.title }}') {
            return I18n::translate($text, $text);
        }
    ],
    'computed' => [
        'columns' => function () {
            $columns = [];

            if ($this->image !== false) {
                $columns['image'] = [
                    'label' => ' ',
                    'type'  => 'image',
                    'width' => 'var(--table-row-height)'
                ];
            }

            $columns['title'] = [
                'label' => 'Title',
                'type'  => 'url'
            ];

            if ($this->info) {
                $columns['info'] = [
                    'label' => 'Info',
                    'type'  => 'text',
                ];
            }

            foreach ($this->columns as $columnName => $column) {
                $column['id']     = $columnName;
                $column['costum'] = true;
                $columns[$columnName . 'Cell'] = $column;
            }

            $columns['flag'] = [
                'label' => ' ',
                'type'  => 'flag',
                'width' => 'var(--table-row-height)'
            ];

            return $columns;
        },
        'parent' => function () {
            $parent = $this->parentModel();

            if (is_a($parent, 'Kirby\Cms\Site') === false && is_a($parent, 'Kirby\Cms\Page') === false) {
                throw new InvalidArgumentException('The parent is invalid. You must choose the site or a page as parent.');
            }

            return $parent;
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

            // filters pages that are protected and not in the templates list
            // internal `filter()` method used instead of foreach loop that previously included `unset()`
            // because `unset()` is updating the original data, `filter()` is just filtering
            // also it has been tested that there is no performance difference
            // even in 0.1 seconds on 100k virtual pages
            $pages = $pages->filter(function ($page) {
                // remove all protected pages
                if ($page->isReadable() === false) {
                    return false;
                }

                // filter by all set templates
                if ($this->templates && in_array($page->intendedTemplate()->name(), $this->templates) === false) {
                    return false;
                }

                return true;
            });

            // search
            if ($this->search === true && empty($this->query) === false) {
                $pages = $pages->search($this->query);
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
            if ($this->layout === 'table') {
                return $this->rows();
            }

            $data = [];

            foreach ($this->pages as $item) {
                $panel       = $item->panel();
                $permissions = $item->permissions();

                $row = [
                    'dragText'    => $panel->dragText(),
                    'id'          => $item->id(),
                    'image'       => $panel->image($this->image, $this->layout),
                    'info'        => $item->toSafeString($this->info ?? false),
                    'link'        => $panel->url(true),
                    'parent'      => $item->parentId(),
                    'permissions' => [
                        'sort'         => $permissions->can('sort'),
                        'changeSlug'   => $permissions->can('changeSlug'),
                        'changeStatus' => $permissions->can('changeStatus'),
                        'changeTitle'  => $permissions->can('changeTitle'),
                    ],
                    'status'      => $item->status(),
                    'template'    => $item->intendedTemplate()->name(),
                    'text'        => $item->toSafeString($this->text),
                ];

                $data[] = $row;
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
            $modelLink  = $this->model->panel()->url(true);
            $parentLink = $this->parent->panel()->url(true);

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

            if (empty($this->query) === false) {
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
        },
        'rows' => function () {

            $rows = [];

            foreach ($this->pages as $item) {

                $panel = $item->panel();
                $row   = [];

                $row['title'] = [
                    'text' => $item->toSafeString($this->text),
                    'href' => $panel->url(true)
                ];

                $row['id']          = $item->id();
                $row['image']       = $panel->image($this->image, 'list');
                $row['info']        = $item->toSafeString($this->info ?? false);
                $row['status']      = $item->status();
                $row['permissions'] = $item->permissions();
                $row['link']        = $panel->url(true);

                // custom columns
                foreach ($this->columns as $columnName => $column) {
                    // don't overwrite essential columns
                    if (isset($row[$columnName]) === true) {
                        continue;
                    }

                    if (empty($column['value']) === false) {
                        $value = $item->toSafeString($column['value']);
                    } else {
                        $value = $item->content()->get($column['id'] ?? $columnName)->value();
                    }

                    $row[$columnName] = $value;
                }

                $rows[] = $row;
            }

            return $rows;

        }
    ],
    'toArray' => function () {

        return [
            'data'    => $this->data,
            'errors'  => $this->errors,
            'options' => [
                'add'      => $this->add,
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
                'sortable' => $this->sortable
            ],
            'pagination' => $this->pagination,
        ];
    }
];
