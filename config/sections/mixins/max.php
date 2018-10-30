<?php

return [
    'props' => [
        /**
         * Sets the maximum number of allowed entries in the section
         */
        'max' => function (int $max = null) {
            return $max;
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
