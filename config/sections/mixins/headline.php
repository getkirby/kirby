<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        'headline' => function ($headline = null) {
            return I18n::translate($headline, $headline);
        }
    ],
    'computed' => [
        'headline' => function () {
            return $this->headline ?? ucfirst($this->name);
        }
    ]
];
