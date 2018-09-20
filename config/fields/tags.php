<?php

return [
    'mixins' => ['options'],
    'props' => [
        'accept' => function ($value = 'all') {
            return V::in($value, ['all', 'options']) ? $value : 'all';
        },
        'icon' => function ($icon = 'tag') {
            return $icon;
        },
        'min' => function (int $min = null) {
            return $min;
        },
        'max' => function (int $max = null) {
            return $max;
        },
        'separator' => function (string $separator = ',') {
            return $separator;
        },
    ],
    'computed' => [
        'options' => function () {
            return $this->getOptions();
        },
        'default' => function (): array {
            return $this->toTags($this->default);
        },
        'value' => function (): array {
            return $this->toTags($this->value);
        }
    ],
    'methods' => [
        'toTags' => function ($value) {

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
            }, Str::split($value));

        }
    ],
    'toString' => function ($value): string {

        if (is_string($value) === true) {
            return $value;
        }

        return A::join(
            array_column($value ?? [], 'value'),
            $this->separator() . ' '
        );
    },
    'validations' => [
        'min',
        'max'
    ]
];
