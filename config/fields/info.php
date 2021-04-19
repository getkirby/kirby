<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * Unset inherited props
         */
        'after'       => null,
        'autofocus'   => null,
        'before'      => null,
        'default'     => null,
        'disabled'    => null,
        'icon'        => null,
        'placeholder' => null,
        'required'    => null,
        'translate'   => null,

        /**
         * Text to be displayed
         */
        'text' => function ($value = null) {
            return I18n::translate($value, $value);
        },

        /**
         * Change the design of the info box
         */
        'theme' => function (string $theme = null) {
            return $theme;
        }
    ],
    'computed' => [
        'text' => function () {
            if ($text = $this->text) {
                $text = $this->model()->toString($text);
                $text = $this->kirby()->kirbytext($text);
                return $text;
            }
        }
    ],
    'save' => false,
];
