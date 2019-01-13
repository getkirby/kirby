<?php

return [
    'props' => [
        /**
         * Sets the minimum number of required entries in the section
         */
        'min' => function (int $min = null) {
            return $min;
        }
    ],
    'methods' => [
        'validateMin' => function () {
            if ($this->min && $this->min > $this->total) {
                return false;
            }

            return true;
        }
    ]
];
