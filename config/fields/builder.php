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
        },

        /**
         * Enables/disables pretty printed JSON
         * in the content files
         *
         * @param bool $pretty
         * @return bool
         */
        'pretty' => function (bool $pretty = true): bool {
            return $pretty;
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
                'pattern' => 'uuid',
                'action'  => function () {
                    return ['uuid' => uuid()];
                }
            ],
            [
                'pattern' => 'fieldsets/(:any)',
                'method'  => 'GET',
                'action'  => function ($type) {
                    $field = $this->field();

                    if (!$fieldset = $field->fieldsets()[$type] ?? null) {
                        throw new NotFoundException('The fieldset type could not be found');
                    }

                    $content = $field->builder->form($fieldset['fields'], [])->values();

                    return [
                        'attrs'   => [],
                        'content' => $content,
                        'id'      => uuid(),
                        'type'    => $type
                    ];
                }
            ],
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
            ],
        ];
    },
    'save' => function ($blocks) {
        $value = [
            'type'   => 'builder',
            'blocks' => $blocks
        ];

        if ($this->pretty === true) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return json_encode($value);
    },
    'validations' => [
        'max'
    ]
];
