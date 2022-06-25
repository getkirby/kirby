<?php

use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

return [
    'props' => [
        /**
         * Columns config for `layout: table`
         */
        'columns' => function (array $columns = null) {
            return $columns ?? [];
        },
        /**
         * Section layout.
         * Available layout methods: `list`, `cardlets`, `cards`, `table`.
         */
        'layout' => function (string $layout = 'list') {
            $layouts = ['list', 'cardlets', 'cards', 'table'];
            return in_array($layout, $layouts) ? $layout : 'list';
        },
        /**
         * The size option controls the size of cards. By default cards are auto-sized and the cards grid will always fill the full width. With a size you can disable auto-sizing. Available sizes: `tiny`, `small`, `medium`, `large`, `huge`
         */
        'size' => function (string $size = 'auto') {
            return $size;
        },
    ],
    'computed' => [
        'columns' => function () {
            $columns = [];

            if ($this->layout !== 'table') {
                return [];
            }

            if ($this->image !== false) {
                $columns['image'] = [
                    'label'  => ' ',
                    'mobile' => true,
                    'type'   => 'image',
                    'width'  => 'var(--table-row-height)'
                ];
            }

            if ($this->text) {
                $columns['title'] = [
                    'label'  => I18n::translate('title'),
                    'mobile' => true,
                    'type'   => 'url',
                ];
            }

            if ($this->info) {
                $columns['info'] = [
                    'label' => I18n::translate('info'),
                    'type'  => 'text',
                ];
            }

            foreach ($this->columns as $columnName => $column) {
                if ($column === true) {
                    $column = [];
                }

                if ($column === false) {
                    continue;
                }

                // fallback for labels
                $column['label'] ??= Str::ucfirst($columnName);

                // make sure to translate labels
                $column['label'] = I18n::translate($column['label'], $column['label']);

                // keep the original column name as id
                $column['id'] = $columnName;

                // add the custom column to the array with a key that won't
                // override the system columns
                $columns[$columnName . 'Cell'] = $column;
            }

            if ($this->type === 'pages') {
                $columns['flag'] = [
                    'label'  => ' ',
                    'mobile' => true,
                    'type'   => 'flag',
                    'width'  => 'var(--table-row-height)',
                ];
            }

            return $columns;
        },
    ],
    'methods' => [
        'columnsValues' => function (array $item, $model) {
            $item['title'] = [
                'text' => $item['text'],
                'href' => $model->panel()->url(true)
            ];

            foreach ($this->columns as $columnName => $column) {
                // don't overwrite essential columns
                if (isset($item[$columnName]) === true) {
                    continue;
                }

                if (empty($column['value']) === false) {
                    if ($column['type'] ?? false === 'html') {
                        $value = $model->toString($column['value']);
                    } else {
                        $value = $model->toSafeString($column['value']);
                    }
                } else {
                    $value = $model->content()->get($column['id'] ?? $columnName)->value();
                }

                $item[$columnName] = $value;
            }

            return $item;
        }
    ],
];
