<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * Sets the help text
         */
        'help' => function ($help = null) {
            return I18n::translate($help, $help);
        }
    ]
];
