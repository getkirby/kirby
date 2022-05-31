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
        'rows' => function () {
            $rows = [];

            foreach ($this->models as $item) {
                $panel = $item->panel();
                $row   = [];

                $row['title'] = [
                    'text' => $item->toSafeString($this->text),
                    'href' => $panel->url(true)
                ];

                $row['id']          = $item->id();
                $row['image']       = $panel->image($this->image, 'list');
                $row['info']        = $item->toSafeString($this->info ?? false);
                $row['permissions'] = $item->permissions();
                $row['link']        = $panel->url(true);

                if ($this->type === 'pages') {
                    $row['status'] = $item->status();
                }

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
];
