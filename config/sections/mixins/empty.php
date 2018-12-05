<?php

return [
    'props' => [
        /**
         * Sets the text for the empty state box
         */
        'empty' => function (string $empty = null) {
            return I18n::translate($empty);
        }
    ],
    'methods' => [
        'isFull' => function () {
            if ($this->max) {
                return $this->total >= $this->max;
            }

            return false;
        },
        'validateMax' => function () {
            if ($this->max && $this->max < $this->total) {
                return false;
            }

            return true;
        }
    ]
];
