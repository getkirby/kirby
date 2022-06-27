<?php

use Kirby\Cms\Helpers;
use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * The headline for the section. This can be a simple string or a template with additional info from the parent page.
         * @todo remove in 3.9.0
         */
        'headline' => function ($headline = null) {
            // TODO: add deprecation notive in 3.8.0
            // if ($headline !== null) {
            //     Helpers::deprecated('`headline` prop for sections has been deprecated and will be removed in Kirby 3.9.0. Use `label` instead.');
            // }

            return I18n::translate($headline, $headline);
        },
        /**
         * The label for the section. This can be a simple string or
         * a template with additional info from the parent page.
         * Replaces the `headline` prop.
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
