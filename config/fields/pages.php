<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;

return [
    'mixins' => ['min'],
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
         * The placeholder text if no pages have been selected yet
         */
        'empty' => function ($empty = null) {
            return I18n::translate($empty, $empty);
        },

        /**
         * Image settings for each item
         */
        'image' => function (array $image = null) {
            return $image ?? [];
        },

        /**
         * Info text
         */
        'info' => function (string $info = null) {
            return $info;
        },

        /**
         * Changes the layout of the selected files. Available layouts: `list`, `cards`
         */
        'layout' => function (string $layout = 'list') {
            return $layout;
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
         * If `false`, only a single page can be selected
         */
        'multiple' => function (bool $multiple = true) {
            return $multiple;
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
         * Main text
         */
        'text' => function (string $text = null) {
            return $text;
        },

        'value' => function ($value = null) {
            return $this->toPages($value);
        },
    ],
    'methods' => [
        'pageResponse' => function ($page) {
            $image = $page->panelImage($this->image);

            return [
                'text'        => $page->toString($this->text ?? '{{ page.title }}'),
                'link'        => $page->panelUrl(true),
                'id'          => $page->id(),
                'info'        => $page->toString($this->info ?? false),
                'image'       => $image,
                'icon'        => $page->panelIcon($image),
                'hasChildren' => $page->hasChildren(),
            ];
        },
        'toPages' => function ($value = null) {
            $pages = [];
            $kirby = kirby();

            foreach (Yaml::decode($value) as $id) {
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
                    $query = $field->query();

                    if ($query) {
                        $pages = $field->model()->query($query, 'Kirby\Cms\Pages');
                        $model = null;
                    } else {
                        if (!$parent = $this->site()->find($this->requestQuery('parent'))) {
                            $parent = $this->site();
                        }

                        $pages = $parent->children();
                        $model = [
                            'id'    => $parent->id() == '' ? null : $parent->id(),
                            'title' => $parent->title()->value()
                        ];
                    }

                    $children = [];

                    foreach ($pages as $index => $page) {
                        if ($page->isReadable() === true) {
                            $children[] = $field->pageResponse($page);
                        }
                    }

                    return [
                        'model' => $model,
                        'pages' => $children
                    ];
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
