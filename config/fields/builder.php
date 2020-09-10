<?php

use Kirby\Cms\Builder;

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
                'pattern' => 'preview',
                'method'  => 'ALL',
                'action'  => function () {
                    dump(get());
                    exit;
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
            'name'      => $this->name,
            'required'  => $this->required,
            'type'      => 'builder',
            'value'     => $this->value,
        ];
    }
];
