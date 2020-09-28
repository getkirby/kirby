<?php

use Kirby\Cms\Block;
use Kirby\Cms\Builder;
use Kirby\Toolkit\I18n;

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
         * The placeholder text if no blocks have been added yet
         */
        'empty' => function ($empty = null) {
            return I18n::translate($empty, $empty);
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
         * Groups fields to move blocks between fields in the same group
         *
         * @param string|null $group
         * @return string|null
         */
        'group' => function (string $group = null) {
            return $group;
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
                    $builder  = $this->field()->builder();
                    $fields   = $builder->fields($type);
                    $defaults = $builder->form($fields, [])->data(true);
                    $content  = $builder->form($fields, $defaults)->values();

                    return Block::factory([
                        'content' => $content,
                        'type'    => $type
                    ])->toArray();
                }
            ],
            [
                'pattern' => 'fieldsets/(:any)/fields/(:any)/(:all?)',
                'method'  => 'ALL',
                'action'  => function (string $fieldsetType, string $fieldName, string $path = null) {
                    $builder = $this->field()->builder();
                    $fields  = $builder->fields($fieldsetType);
                    $field   = $builder->form($fields)->field($fieldName);

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
        return $this->builder->toJson($blocks, $this->pretty);
    },
    'validations' => [
        'max'
    ]
];
