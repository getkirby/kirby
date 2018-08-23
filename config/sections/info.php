<?php

use Kirby\Toolkit\I18n;

return [
    'mixins' => [
        'headline'
    ],
    'props' => [
        'text' => function (string $text = null) {
            return I18n::translate($text, $text);
        },
        'theme' => function (string $theme = null) {
            return $theme;
        }
    ],
    'computed' => [
        'text' => function () {
            if ($this->text) {
                $text = $this->model()->toString($this->text);
                $text = $this->kirby()->kirbytext($text);

                return $text;
            }
        },
    ],
    'toArray' => function () {
        return [
            'options' => [
                'headline' => $this->headline,
                'text'     => $this->text,
                'theme'    => $this->theme
            ]
        ];
    }
];


