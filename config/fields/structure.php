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
        }
    ],
    'methods' => [
        'form' => function (array $values = []) {
            return new Form($this->props['fields'], $values, $this->data);
        }
    ],
    'validations' => [
        'required',
        'min',
        'max'
    ]
];
