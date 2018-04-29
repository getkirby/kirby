<?php

return [
    'props' => [
        'default'  => $this->required() ? 'today': null,
        'icon'     => 'calendar',
        'format'   => DATE_W3C,
        'max'      => null,
        'min'      => null,
        'required' => false,
        'time'     => false
    ],
    'methods' => [
        'toApi' => function ($value) {
            if ($date = strtotime($value)) {
                return date(DATE_W3C, $date);
            }

            return null;
        },
        'toString' => function ($value): string {
            if ($date = strtotime($value)) {
                return date($this->format(), $date);
            }

            return '';
        },
        'validate' => function () {
            $this->validate('required');
            $this->validate('date');
        }
    ]
];
