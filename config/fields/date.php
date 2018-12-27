<?php

return [
    'props' => [
        /**
         * Default date when a new Page/File/User gets created
         */
        'default' => function ($default = null) {
            return $default;
        },
        /**
         * Changes the calendar icon to something custom
         */
        'icon' => function (string $icon = "calendar") {
            return $icon;
        },
        /**
         * Youngest date, which can be selected/saved
         */
        'max' => function (string $max = null) {
            return $this->toDate($max);
        },
        /**
         * Oldest date, which can be selected/saved
         */
        'min' => function (string $min = null) {
            return $this->toDate($min);
        },
        /**
         * The placeholder is not available
         */
        'placeholder' => null,
        /**
         * Pass true or an array of time field options to show the time selector.
         */
        'time' => function ($time = false) {
            return $time;
        },
        /**
         * Must be a parseable date string
         */
        'value' => function ($value = null) {
            return $value;
        },
    ],
    'computed' => [
        'default' => function () {
            return $this->toDate($this->default);
        },
        'format' => function () {
            return $this->props['format'] ?? ($this->time() === false ? 'Y-m-d' : 'Y-m-d H:i');
        },
        'value' => function () {
            return $this->toDate($this->value);
        },
    ],
    'methods' => [
        'toDate' => function ($value) {
            if ($timestamp = timestamp($value, $this->time['step'] ?? 5)) {
                return date(DATE_W3C, $timestamp);
            }

            return null;
        }
    ],
    'save' => function ($value) {
        if ($value !== null && $date = strtotime($value)) {
            return date($this->format(), $date);
        }

        return '';
    },
    'validations' => [
        'date'
    ]
];
