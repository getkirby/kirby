<?php

use Kirby\Sane\Html;

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
         * @param array|bool $marks
         */
        'marks' => function ($marks = true) {
            return $marks;
        },
        /**
         * Sets the allowed nodes. Available nodes: `bulletList`, `orderedList`, `heading`, `horizontalRule`, `listItem`. Activate/deactivate them all by passing `true`/`false`. Default nodes are `heading`, `bulletList`, `orderedList`.
         * @param array|bool|null $nodes
         */
        'nodes' => function ($nodes = null) {
            return $nodes;
        }
    ],
    'computed' => [
        'value' => function () {
            return Html::sanitize(trim($this->value));
        }
    ],
];
