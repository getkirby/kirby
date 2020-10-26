<?php

use Kirby\Cms\Block;
use Kirby\Cms\BlocksField;
use Kirby\Data\Data;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

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


        'layouts' => function (array $layouts = []) {
            return array_map(function ($layout) {
                return Str::split($layout);
            }, $layouts);
        },
        'value' => function ($value) {
            return Data::decode($value, 'json');
        },
    ],
    'computed' => [
        'blocksField' => function () {
            return new BlocksField($this->model, [
                'fieldsets' => $this->props['fieldsets'] ?? []
            ]);
        },
        'fieldsets' => function () {
            return $this->blocksField->fieldsets();
        },
        'value' => function () {
            $value = $this->value;

            foreach ($value as $layoutIndex => $layout) {
                foreach ($layout['columns'] as $columnIndex => $column) {
                    $value[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksField->value($column['blocks'], false);
                }
            }

            return $value;
        }
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
    'save' => function ($value) {
        foreach ($value as $layoutIndex => $layout) {
            foreach ($layout['columns'] as $columnIndex => $column) {
                $value[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksField->toArray($column['blocks'] ?? []);
            }
        }

        return json_encode($value);
    }
];
