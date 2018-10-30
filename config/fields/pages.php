<?php

use Kirby\Toolkit\A;

return [
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
         * Default selected page(s) when a new Page/File/User is created
         */
        'default' => function ($default = null) {
            return $this->toPages($default);
        },
        /**
         * The minimum number of required selected pages
         */
        'min' => function (int $min = null) {
            return $min;
        },
        /**
         * The maximum number of allowed selected pages
         */
        'max' => function (int $max = null) {
            return $max;
        },
        /**
         * If false, only a single page can be selected
         */
        'multiple' => function (bool $multiple = true) {
            return $multiple;
        },
        'value' => function ($value = null) {
            return $this->toPages($value);
        },
    ],
    'methods' => [
        'toPages' => function ($value = null) {

            $pages = [];
            $kirby = kirby();

            foreach (Yaml::decode($value) as $id) {

                if (is_array($id) === true) {
                    $id = $id['id'] ?? null;
                }

                if ($id !== null && ($page = $kirby->page($id))) {
                    $pages[] = [
                        'title' => $page->title()->value(),
                        'id'    => $page->id(),
                    ];
                }
            }

            return $pages;

        }
    ],
    'save' => function ($value = null) {
        return A::pluck($value, 'id');
    },
    'validations' => [
        'max',
        'min'
    ]
];
