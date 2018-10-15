<?php

use Kirby\Toolkit\A;

return [
    'props' => [
        'default' => function ($default = null) {
            return $this->toPages($default);
        },
        'min' => function (int $min = null) {
            return $min;
        },
        'max' => function (int $max = null) {
            return $max;
        },
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
