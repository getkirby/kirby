<?php

use Kirby\Sane\Sane;

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
         * Sets the allowed HTML formats. Available formats: `bold`, `italic`, `underline`, `strike`, `code`, `link`, `email`. Activate them all by passing `true`. Deactivate them all by passing `false`
         * @param array|bool $marks
         */
        'marks' => function ($marks = true) {
            return $marks;
        },
        /**
         * Sets the allowed nodes. Available nodes: `paragraph`, `heading`, `bulletList`, `orderedList`. Activate/deactivate them all by passing `true`/`false`. Default nodes are `paragraph`, `heading`, `bulletList`, `orderedList`.
         * @param array|bool|null $nodes
         */
        'nodes' => function ($nodes = null) {
            return $nodes;
        }
    ],
    'computed' => [
        'value' => function () {
            $value = trim($this->value ?? '');
            return Sane::sanitize($value, 'html');
        }
    ],
];
