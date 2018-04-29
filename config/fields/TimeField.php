<?php

use Kirby\Exception\InvalidArgumentException;

return [
    'props' => [
        'default'  => $this->required() ? 'now': null,
        'icon'     => 'clock',
        'format'   => $this->notation() === 24 ? 'H:i' : 'h:i a',
        'notation' => function ($value = null) {
            // Default value
            $value = $value ?? 24;

            // Validate notation prop value
            if (in_array($value, [12, 24], true) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.time.notation'
                ]);
            }

            return $value;
        },
        'required' => false,
        'step'     => 5
    ],
    'methods' => [
        'toApi' => function ($value) {
            if ($timestamp = strtotime($value)) {
                return date('H:i', $timestamp);
            }

            return null;
        },
        'toString' => function ($value): string {
            if ($timestamp = strtotime($value)) {
                return date($this->format(), $timestamp);
            }

            return '';
        },
        'validate' => function () {
            $this->validate('required');
            $this->validate('time');
        }
    ]
];
