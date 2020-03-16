<?php

use Kirby\Data\Yaml;
use Kirby\Toolkit\A;

return [
    'mixins' => [
        'min',
        'pagepicker',
        'picker'
    ],
    'props' => [
        /**
         * Unset inherited props
         */
        'after'       => null,
        'autofocus'   => null,
        'before'      => null,
        'icon'        => null,
        'placeholder' => null,

        /**
         * Default selected page(s) when a new page/file/user is created
         */
        'default' => function ($default = null) {
            return $this->toPages($default);
        },

        /**
         * Changes the layout of the selected files. Available layouts: `list`, `cards`
         */
        'layout' => function (string $layout = 'list') {
            return $layout;
        },

        /**
         * Optional query to select a specific set of pages
         */
        'query' => function (string $query = null) {
            return $query;
        },

        /**
         * Layout size for cards: `tiny`, `small`, `medium`, `large` or `huge`
         */
        'size' => function (string $size = 'auto') {
            return $size;
        },

        /**
         * Optionally include subpages of pages
         */
        'subpages' => function (bool $subpages = true) {
            return $subpages;
        },

        'value' => function ($value = null) {
            return $this->toPages($value);
        },
    ],
    'computed' => [
        /**
         * Unset inherited computed
         */
        'default' => null
    ],
    'methods' => [
        'pageResponse' => function ($page) {
            $response = $page->panelPickerData([
                'image' => $this->image,
                'info'  => $this->info,
                'text'  => $this->text,
            ]);

            if ($this->primaryKey !== null && empty($response[$this->primaryKey]) === true) {
                $response[$this->primaryKey] = $page->content()->get($this->primaryKey)->value();
            }

            return $response;
        },
        'toPages' => function ($value = null) {
            $pages = [];

            foreach (Yaml::decode($value) as $data) {
                $id = is_array($data) === true ? ($data['id'] ?? null) : $data;

                if (empty($id) === false) {
                    if ($this->primaryKey === null || is_array($data) === true) {
                        $page = $this->kirby()->page($id);
                    } else {
                        $parent = $this->parent() ?? $this->kirby()->site();
                        $page = $parent->index(true)->findBy($this->primaryKey, $id);
                    }

                    if ($page) {
                        $pages[] = $this->pageResponse($page);
                    }
                }
            }

            return $pages;
        }
    ],
    'api' => function () {
        return [
            [
                'pattern' => '/',
                'action' => function () {
                    $field = $this->field();

                    return $field->pagepicker([
                        'image'    => $field->image(),
                        'info'     => $field->info(),
                        'limit'    => $field->limit(),
                        'page'     => $this->requestQuery('page'),
                        'parent'   => $this->requestQuery('parent'),
                        'query'    => $field->query(),
                        'search'   => $this->requestQuery('search'),
                        'subpages' => $field->subpages(),
                        'text'     => $field->text()
                    ]);
                }
            ]
        ];
    },
    'save' => function ($value = null) {
        return A::pluck($value, $this->primaryKey ?? 'id');
    },
    'validations' => [
        'max',
        'min'
    ]
];
