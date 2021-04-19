<?php

return [
    'props' => [
        /**
         * Enables inline mode, which will not wrap new lines in paragraphs and creates hard breaks instead.
         *
         * @param bool $inline
         */
        'inline' => function (bool $inline = false) {
            return $inline;
        },
        /**
         * Sets the allowed HTML formats. Available formats: `bold`, `italic`, `underline`, `strike`, `code`, `link`. Activate them all by passing `true`. Deactivate them all by passing `false`
         */
        'marks' => function ($marks = true) {
            return $marks;
        }
    ]
];
