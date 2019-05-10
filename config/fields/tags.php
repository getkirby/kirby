<?php

return [
    'mixins' => ['min', 'options'],
    'props' => [

        /**
         * Unset inherited props
         */
        'after'       => null,
        'before'      => null,
        'placeholder' => null,

        /**
         * If set to `all`, any type of input is accepted. If set to `options` only the predefined options are accepted as input.
         */
        'accept' => function ($value = 'all') {
            return V::in($value, ['all', 'options']) ? $value : 'all';
        },
        /**
         * Changes the tag icon
         */
        'icon' => function ($icon = 'tag') {
            return $icon;
        },
        /**
         * Minimum number of required entries/tags
         */
        'min' => function (int $min = null) {
            return $min;
        },
        /**
         * Maximum number of allowed entries/tags
         */
        'max' => function (int $max = null) {
            return $max;
        },
        /**
         * Custom tags separator, which will be used to store tags in the content file
         */
        'separator' => function (string $separator = ',') {
            return $separator;
        },
    ],
    'computed' => [
        'default' => function (): array {
            return $this->toTags($this->default);
        },
        'value' => function (): array {
            return $this->toTags($this->value);
        }
    ],
    'methods' => [
        'toTags' => function ($value) {
            if (is_null($value) === true) {
                return [];
            }

            $options = $this->options();

            // transform into value-text objects
            return array_map(function ($option) use ($options) {

                // already a valid object
                if (is_array($option) === true && isset($option['value'], $option['text']) === true) {
                    return $option;
                }

                $index = array_search($option, array_column($options, 'value'));

                if ($index !== false) {
                    return $options[$index];
                }

                return [
                    'value' => $option,
                    'text'  => $option,
                ];
            }, Str::split($value, $this->separator()));
        }
    ],
    'save' => function (array $value = null): string {
        return A::join(
            A::pluck($value, 'value'),
            $this->separator() . ' '
        );
    },
    'validations' => [
        'min',
        'max'
    ]
];
