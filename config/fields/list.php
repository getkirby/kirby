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
    'save' => function ($value): string {
        // removes `<p>` and `</p>` tags from value
        return str_replace(['<p>', '</p>'], '', $value);
    }
];
