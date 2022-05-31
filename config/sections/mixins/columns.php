<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * Columns config for `layout: table`
         */
        'columns' => function (array $columns = null) {
            return $columns ?? [];
        },
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
                'label' => I18n::translate('title'),
                'type'  => 'url'
            ];

            if ($this->info) {
                $columns['info'] = [
                    'label' => 'Info',
                    'type'  => 'text',
                ];
            }

            foreach ($this->columns as $columnName => $column) {
                $column['id'] = $columnName;
                $columns[$columnName . 'Cell'] = $column;
            }

            if ($this->type === 'pages') {
                $columns['flag'] = [
                    'label' => ' ',
                    'type'  => 'flag',
                    'width' => 'var(--table-row-height)'
                ];
            }

            return $columns;
        },
    ],
    'methods' => [
        'columnsValues' => function ($item, $model) {
            $item['title'] = [
                'text' => $model->toSafeString($this->text),
                'href' => $model->panel()->url(true)
            ];

            foreach ($this->columns as $columnName => $column) {
                // don't overwrite essential columns
                if (isset($item[$columnName]) === true) {
                    continue;
                }

                if (empty($column['value']) === false) {
                    $value = $model->toSafeString($column['value']);
                } else {
                    $value = $model->content()->get($column['id'] ?? $columnName)->value();
                }

                $item[$columnName] = $value;
            }

            return $item;
        }
    ],
];
