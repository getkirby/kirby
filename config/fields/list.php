<?php

return [
    'props' => [
        /**
         * Sets the allowed HTML formats. Available formats: `bold`, `italic`, `underline`, `strike`, `code`, `link`. Activate them all by passing `true`. Deactivate them all by passing `false`
         */
        'marks' => function ($marks = true) {
            return $marks;
        }
    ],
    'computed' => [
        'value' => function () {
            return trim($this->value ?? '');
        }
    ]
];
