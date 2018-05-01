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
        'value' => function ($value = null) {
            $tags = Str::split($value);

            // transform into value-text objects
            $tags = array_map(function ($tag) {

                // already a valid tag
                if (is_array($tag) === true && isset($tag['value'], $tag['text']) === true) {
                    return $tag;
                }

                if (is_string($tag) === true) {

                    // TODO: apply options

                    return [
                        'value' => $tag,
                        'text'  => $tag,
                    ];
                }

                return null;

            }, $tags);

            return $tags;
        }
    ],
    'methods' => [
        'toString' => function ($value): string {
            return A::join(array_column($value, 'value'), $this->separator() . ' ');
        },
    ],
    'validations' => [
        'required',
        'min',
        'max'
    ]
];
