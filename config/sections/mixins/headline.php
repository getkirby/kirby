<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * The headline for the section. This can be a simple string or a template with additional info from the parent page.
         * @todo deprecate in 3.7
         */
        'headline' => function ($headline = null) {
            return I18n::translate($headline, $headline);
        },
        /**
         * label is the new official replacement for headline
         */
        'label' => function ($label = null) {
            return I18n::translate($label, $label);
        }
    ],
    'computed' => [
        'headline' => function () {
            if ($this->headline) {
                return $this->model()->toString($this->headline);
            }

            if ($this->label) {
                return $this->model()->toString($this->label);
            }

            return ucfirst($this->name);
        }
    ]
];
