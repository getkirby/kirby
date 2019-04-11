<?php

return [
    'props' => [
        /**
         * Sets the text for the empty state box
         */
        'empty' => function ($empty = null) {
            return I18n::translate($empty, $empty);
        }
    ]
];
