<?php

use Kirby\Form\Form;
use Kirby\Cms\Blueprint;

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
        'default' => function () {
            return $this->rows($this->default);
        },
        'value' => function () {
            return $this->rows($this->value);
        },
        'fields' => function () {
            return $this->form()->fields()->toArray();
        }
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
            return new Form(array_merge([
                'fields' => $this->fields,
                'values' => $values,
                'model'  => $this->model() ?? null
            ], $this->props));
        },
    ],
    'toString' => function () {
        $strings = [];

        foreach ($this->value() as $row) {
            $strings[] = $this->form($row)->strings();
        }

        return Yaml::encode($strings);
    },
    'validations' => [
        'min',
        'max'
    ]
];
