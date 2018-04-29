<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\V;

return [
    'props' => [
        'accept'    => function ($value = null) {
            $value = $value ?? 'all';

            if (V::in($value, ['all', 'options']) === false) {
                throw new InvalidArgumentException(sprintf('"%s" is not a valid value for the "accept" option', $value));
            }

            return $value;
        },
        'icon'      => 'tag',
        'options'   => null,
        'required'  => false,
        'separator' => ','
    ],
    'methods' => [
        'toApi' => function ($value): array {
            $value = $this->valueFromList($value, $this->separator());

            // transform into value-text objects
            $value = array_map(function ($tag) {
                $option = $this->option($tag['value'] ?? $tag);

                return [
                    'value' => $tag['value'] ?? $tag,
                    'text'  => $option['text'] ?? $tag['text'] ?? $tag,
                ];
            }, $value);

            return $value;
        },
        'toString' => function ($value): string {
            $value = array_column($value, 'value');
            return $this->valueToList($value, $this->separator() . ' ');
        },
        'validate' => function () {
            $this->validate('required');
        }
    ]
];
