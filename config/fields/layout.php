<?php

use Kirby\Cms\Block;
use Kirby\Cms\BlocksField;
use Kirby\Toolkit\I18n;

return [
    'props' => [

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
        'fieldsets' => function (array $fieldsets = []) {
            return $fieldsets;
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
            return [];
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
        return json_encode($blocks);
        return $this->blocksField->toJson($blocks, $this->pretty);
    },
    'validations' => [
        'blocks' => function ($value) {
            return $this->blocksField->validate($value);
        }
    ]
];
