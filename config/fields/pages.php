<?php

use Kirby\Data\Data;
use Kirby\Toolkit\A;

return [
    'mixins' => ['min', 'pagepicker', 'picker'],
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
        }
    ],
    'computed' => [
        'default' => function () {
            return $this->toPages($this->default);
        },
        'value' => function () {
            return $this->toPages($this->value);
        }
    ],
    'methods' => [
        'pageResponse' => function ($page) {
            return $page->panel()->pickerData([
                'image'  => $this->image,
                'info'   => $this->info,
                'layout' => $this->layout,
                'text'   => $this->text,
            ]);
        },
        'toPages' => function ($value = null) {
            $pages = [];
            $kirby = kirby();

            foreach (Data::decode($value, 'yaml') as $id) {
                if (is_array($id) === true) {
                    $id = $id['id'] ?? null;
                }

                if ($id !== null && ($page = $kirby->page($id))) {
                    $pages[] = $this->pageResponse($page);
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
                        'image'    => $field->image([], $field->layout),
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
        return A::pluck($value, 'id');
    },
    'validations' => [
        'max',
        'min'
    ]
];
