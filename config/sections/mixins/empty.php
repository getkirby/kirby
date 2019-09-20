<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * Sets the text for the empty state box
         */
        'empty' => function ($empty = null) {
            return I18n::translate($empty, $empty);
        }
    ],
    'computed' => [
        'empty' => function () {
            if ($this->empty) {
                return $this->model()->toString($this->empty);
            }
        }
    ]
];
