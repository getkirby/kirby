<?php

use Kirby\Cms\Form;
use Kirby\Data\Yaml;

return [
    'mixins' => ['min'],
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
         * Blocks will be placed in a grid
         */
        'columns' => function (int $columns = null) {
            return $columns;
        },

        /**
         * Set the default blocks for the builder
         */
        'default' => function (array $default = null) {
            return $default;
        },

        /**
         * Fieldsets setup for the builder block forms.
         */
        'fieldsets' => function (array $fieldsets) {
            return $fieldsets;
        },

        /**
         * Maximum allowed blocks in the builder.
         * Afterwards the "Add" button will be switched off.
         */
        'max' => function (int $max = null) {
            return $max;
        },
    ],
    'computed' => [
        'default' => function () {
            return $this->blocks($this->default);
        },
        'value' => function () {
            return $this->blocks($this->value);
        },
        'fieldsets' => function () {
            if (empty($this->fieldsets) === true) {
                throw new Exception('Please provide some fieldsets for the builder');
            }

            return $this->fieldsets;
        }
    ],
    'methods' => [
        'blocks' => function ($value) {
            $blocks   = Yaml::decode($value);
            $value    = [];

            foreach ($blocks as $index => $block) {
                if (is_array($block) === false) {
                    continue;
                }

                $fieldset = $this->fieldset($block['type']);
                $fields   = $fieldset['fields'];
                $value[]  = $this->form($block['value'], $fields)->values();
            }

            return $value;
        },
        'fieldset' => function (string $type) {
            if (isset($this->fieldsets()[$type]) === false) {
                throw new Exception('Unknown fieldset: ' . $type);
            }

            return $this->fieldsets[$type];
        },
        'form' => function (array $values = [], array $fields) {
            return new Form([
                'fields' => $fields,
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
                    return true;
                }
            ]
        ];
    },
    'save' => function ($value) {
        $data = [];

        foreach ($value as $block) {
            $data[] = $this->form($block)->data();
        }

        return $data;
    },
    'validations' => [
        'max'
    ]
];
