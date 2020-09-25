<?php

use Kirby\Cms\Block;
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
                    $form    = $builder->form($fields);

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
        return $this->builder->toJson($blocks, $this->pretty);
    },
    'validations' => [
        'max'
    ]
];
