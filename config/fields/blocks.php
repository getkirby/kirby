<?php

use Kirby\Cms\Block;
use Kirby\Cms\BlocksField;
use Kirby\Toolkit\I18n;

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
        'fieldsets' => function (array $fieldsets = null) {
            return $fieldsets;
        },

        /**
         * Optional groups for the fieldset selector
         */
        'fieldsetGroups' => function (array $fieldsetGroups = null) {
            return $fieldsetGroups ?? option('blocks.fieldsetGroups');
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
         * Require at least the $min number of blocks
         *
         * @param int|null $min
         * @return int
         */
        'min' => function (?int $min = null) {
            return $min;
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
        'blocksField' => function () {
            return new BlocksField($this->model, $this->props);
        },
        'fieldsets' => function () {
            return $this->blocksField->fieldsets();
        },
        'value' => function () {
            return $this->blocksField->value();
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
                    $blocksField = $this->field()->blocksField();
                    $fields      = $blocksField->fields($type);
                    $defaults    = $blocksField->form($fields, [])->data(true);
                    $content     = $blocksField->form($fields, $defaults)->values();

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
                    $blocksField = $this->field()->blocksField();
                    $fields      = $blocksField->fields($fieldsetType);
                    $field       = $blocksField->form($fields)->field($fieldName);

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
        return $this->blocksField->toJson($blocks, $this->pretty);
    },
    'validations' => [
        'blocks' => function ($value) {
            return $this->blocksField->validate($value);
        }
    ]
];
