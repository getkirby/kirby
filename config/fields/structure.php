<?php

use Kirby\Form\Form;
use Kirby\Cms\Blueprint;

return [
    'props' => [
        /**
         * Unset inherited props
         */
        'after'       => null,
        'before'      => null,
        'autofocus'   => null,
        'icon'        => null,
        'placeholder' => null,

        /**
         * Optional columns definition to only show selected fields in the structure table.
         */
        'columns' => function (array $columns = []) {
            // lower case all keys, because field names will
            // be lowercase as well.
            return array_change_key_case($columns);
        },
        /**
         * Fields setup for the structure form. Works just like fields in regular forms.
         */
        'fields' => function (array $fields) {
            return $fields;
        },
        /**
         * The number of entries that will be displayed on a single page. Afterwards pagination kicks in.
         */
        'limit' => function (int $limit = null) {
            return $limit;
        },
        /**
         * Maximum allowed entries in the structure. Afterwards the "Add" button will be switched off.
         */
        'max' => function (int $max = null) {
            return $max;
        },
        /**
         * Minimum required entries in the structure
         */
        'min' => function (int $min = null) {
            return $min;
        },
        /**
         * Toggles drag & drop sorting
         */
        'sortable' => function (bool $sortable = null) {
            return $sortable;
        },
        /**
         * Sorts the entries by the given field and order (i.e. title desc)
         * Drag & drop is disabled in this case
         */
        'sortBy' => function (string $sort = null) {
            return $sort;
        }
    ],
    'computed' => [
        'default' => function () {
            return $this->rows($this->default);
        },
        'value' => function () {
            return $this->rows($this->value);
        },
        'fields' => function () {
            return $this->form()->fields()->toArray();
        },
        'columns' => function () {
            $columns = [];

            if (empty($this->columns)) {
                foreach ($this->fields as $field) {

                    // Skip hidden fields.
                    // They should never be included as column
                    if ($field['type'] === 'hidden') {
                        continue;
                    }

                    $columns[$field['name']] = [
                        'type'  => $field['type'],
                        'label' => $field['label'] ?? $field['name']
                    ];
                }
            } else {
                foreach ($this->columns as $columnName => $columnProps) {
                    if (is_array($columnProps) === false) {
                        $columnProps = [];
                    }

                    $field = $this->fields[$columnName] ?? null;

                    if (empty($field) === true) {
                        continue;
                    }

                    $columns[$columnName] = array_merge($columnProps, [
                        'type'  => $field['type'],
                        'label' => $field['label'] ?? $field['name']
                    ]);
                }
            }

            return $columns;
        },
    ],
    'methods' => [
        'rows' => function ($value) {
            $rows  = Yaml::decode($value);
            $value = [];

            foreach ($rows as $index => $row) {
                if (is_array($row) === false) {
                    continue;
                }

                $value[] = $this->form($row)->values();
            }

            return $value;
        },
        'form' => function (array $values = []) {
            return new Form([
                'fields' => $this->attrs['fields'],
                'values' => $values,
                'model'  => $this->model
            ]);
        },
    ],
    'api' => function () {
        return [
            [
                'pattern' => 'validate',
                'method'  => 'ALL',
                'action'  => function () {
                    return array_values($this->field()->form($this->requestBody())->errors());
                }
            ]
        ];
    },
    'save' => function () {
        $data = [];

        foreach ($this->value() as $row) {
            $data[] = $this->form($row)->data();
        }

        return $data;
    },
    'validations' => [
        'min',
        'max'
    ]
];
