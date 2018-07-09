<?php

use Kirby\Form\Form;

return [
    'props' => [
        'fields' => function (array $fields) {
            return $fields;
        },
        'min' => function (int $min = null) {
            return $min;
        },
        'max' => function (int $max = null) {
            return $max;
        },
    ],
    'computed' => [
        'value' => function () {
            $rows  = Yaml::decode($this->props['value']);
            $value = [];

            foreach ($rows as $index => $row) {
                if (is_array($row) === false) {
                    continue;
                }

                $value[] = $this->form($row)->values();
            }

            return $value;
        },
        'fields' => function () {
            $form = new Form([
                'fields' => $this->props['fields'],
                'model'  => $this->data['model'] ?? null
            ]);

            return $form->fields()->toArray();
        }
    ],
    'methods' => [
        'form' => function (array $values = []) {
            return new Form(array_merge([
                'fields' => $this->props['fields'],
                'values' => $values,
                'model'  => $this->data['model'] ?? null
            ], $this->data));
        }
    ],
    'validations' => [
        'required',
        'min',
        'max'
    ]
];
