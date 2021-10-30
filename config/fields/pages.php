<?php

use Kirby\Data\Data;
use Kirby\Toolkit\A;

return [
    'mixins' => [
        'layout',
        'min',
        'pagepicker',
        'picker',
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
         * Optional query to select a specific set of pages
         */
        'query' => function (string $query = null) {
            return $query;
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
                        'image'    => $field->image(),
                        'info'     => $field->info(),
                        'layout'   => $field->layout(),
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
