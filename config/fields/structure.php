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
            return $columns;
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
         * Minimum required entries in the structure
         */
        'min' => function (int $min = null) {
            return $min;
        },
        /**
         * Maximum allowed entries in the structure. Afterwards the "Add" button will be switched off.
         */
        'max' => function (int $max = null) {
            return $max;
        },
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
