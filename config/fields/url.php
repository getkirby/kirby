<?php

return [
    'extends' => 'text',
    'props' => [
        /**
         * Unset inherited props
         */
        'converter'  => null,
        'counter'    => null,
        'spellcheck' => null,

        /**
         * Sets the HTML5 autocomplete attribute
         */
        'autocomplete' => function (string $autocomplete = 'url') {
            return $autocomplete;
        },

        /**
         * Changes the link icon
         */
        'icon' => function (string $icon = 'url') {
            return $icon;
        },

        /**
         * Sets custom placeholder text, when the field is empty
         */
        'placeholder'  => function ($value = null) {
            return I18n::translate($value, $value) ?? 'https://example.com';
        }
    ],
    'validations' => [
        'minlength',
        'maxlength',
        'url'
    ],
];
