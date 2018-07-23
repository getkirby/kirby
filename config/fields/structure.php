<?php

use Kirby\Form\Form;
use Kirby\Cms\Blueprint;

return [
    'props' => [
        'fields' => function (array $fields) {
            return array_map(function ($field) {
                return Blueprint::extend($field);
            }, $fields);
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
            return $this->form()->fields()->toArray();
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
