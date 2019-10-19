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
    ],
    'computed' => [
        'help' => function () {
            if ($this->help) {
                $help = $this->model()->toString($this->help);
                $help = $this->kirby()->kirbytext($help);
                return $help;
            }
        }
    ]
];
