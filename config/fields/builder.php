<?php

use Kirby\Cms\Builder;
use Kirby\Exception\NotFoundException;

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
         * Number of columns for builder blocks
         *
         * @param int $columns 1
         * @return int
         */
        'columns' => function (int $columns = 1) {
            return $columns;
        },

        /**
         * Fieldset definitions
         *
         * @param array $fieldsets
         * @return array
         */
        'fieldsets' => function (array $fieldsets) {
            return $fieldsets;
        },

        /**
         * Only allow the given maximum number of blocks
         *
         * @param int|null $max
         * @return int
         */
        'max' => function (?int $max = null) {
            return $max;
        }
    ],
    'computed' => [
        'builder' => function () {
            return new Builder($this->model, $this->props);
        },
        'fieldsets' => function () {
            return $this->builder->fieldsets();
        },
        'value' => function () {
            return $this->builder->value();
        },
    ],
    'api' => function () {
        return [
            [
                'pattern' => 'fieldsets/(:any)/fields/(:any)/(:all?)',
                'method'  => 'ALL',
                'action'  => function (string $fieldsetName, string $fieldName, string $path = null) {
                    $parent    = $this->field();
                    $fieldsets = $parent->fieldsets();
                    $builder   = $parent->builder();
                    $fieldset  = $fieldsets[$fieldsetName] ?? [];

                    if (empty($fieldset) === true) {
                        throw new NotFoundException('The fieldset could not be found');
                    }

                    $form  = $builder->form($fieldset['fields'] ?? []);

                    if (!$field = $form->fields()->$fieldName()) {
                        throw new NotFoundException('The field could not be found');
                    }

                    $fieldApi = $this->clone([
                        'routes' => $field->api(),
                        'data'   => array_merge($this->data(), ['field' => $field])
                    ]);

                    return $fieldApi->call($path, $this->requestMethod(), $this->requestData());
                }
            ]
        ];
    },
    'toArray' => function () {
        return [
            'columns'   => $this->columns,
            'fieldsets' => $this->fieldsets,
            'help'      => $this->help,
            'label'     => $this->label,
            'max'       => $this->max,
            'name'      => $this->name,
            'required'  => $this->required,
            'type'      => 'builder',
            'value'     => $this->value,
        ];
    },
    'validations' => [
        'max'
    ]
];
