<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        'text' => function ($value = null) {
            return I18n::translate($value, $value);
        },
    ],
    'computed' => [
        'text' => function () {
            $text = $this->text;

            if ($model = $this->model()) {
                $text = $this->model()->toString($text);
            }

            return kirbytext($text);
        }
    ],
    'save' => false,
];
