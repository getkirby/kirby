<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * The headline for the section. This can be a simple string or a template with additional info from the parent page.
         */
        'headline' => function ($headline = null) {
            return I18n::translate($headline, $headline);
        }
    ],
    'computed' => [
        'headline' => function () {
            if ($this->headline) {
                return $this->model()->toString($this->headline);
            }

            return ucfirst($this->name);
        }
    ]
];
