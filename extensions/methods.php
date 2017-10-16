<?php

use Kirby\Cms\App;
use Kirby\Fields\Field;
use Kirby\Toolkit\Str;

/**
 * Field method setup
 */
Field::method([

    // states
    'isEmpty' => function () {
        return empty($this->value());
    },
    'isFalse' => function () {
        return $this->toBool() === false;
    },
    'isNotEmpty' => function () {
        return $this->isEmpty() === false;
    },
    'isTrue' => function () {
        return $this->toBool() === true;
    },

    // converters
    'toArray' => function ($separator) {
        return $this->split($separator);
    },
    'toBool' => function ($default = false) {
        $value = $this->isEmpty() ? $default : $this->value();
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    },
    'toExcerpt' => function () {

    },
    'toFile' => function () {
        return $this->page()->file($this->value());
    },
    'toFloat' => function ($default = 0) {
        $value = $this->isEmpty() ? $default : $this->value();
        return floatval($value);
    },
    'toInt' => function ($default = 0) {
        $value = $this->isEmpty() ? $default : $this->value();
        return intval($value);
    },
    'toLink' => function () {

    },
    'toPage' => function () {
        return App::instance()->site()->find($this->value());
    },
    'toPages' => function () {

    },
    'toStructure' => function () {

    },
    'toUrl' => function () {

    },

    // inspectors
    'length' => function () {
        return Str::length($this->value());
    },

    // manipulators
    'escape' => function () {

    },
    'html' => function (bool $keepTags = true) {
        return $this;
    },
    'kirbytext' => function () {
        return $this->value(function($value) {
            return App::instance()->kirbytext()->parse((string)$value);
        })->markdown();
    },
    'kirbytags' => function () {

    },
    'lower' => function () {
        return $this->value(function($value) {
            return Str::lower($value);
        });
    },
    'markdown' => function () {
        return $this->value(function($value) {
            return App::instance()->markdown()->parse((string)$value);
        });
    },
    'or' => function ($fallback = null) {

        if ($field->isNotEmpty()) {
            return $this;
        }

        if (is_a($fallback, Field::class)) {
            return $fallback;
        }

        return $this->value(function ($value) use ($fallback) {
            return $fallback;
        });

    },
    'short' => function (int $length, string $appendix = '…') {
        return $this->value(function ($value) use ($length, $appendix) {
            return Str::short($this->value(), $length, $appendix);
        });
    },
    'smartypants' => function () {
        return $this->value(function($value) {
            return App::instance()->smartypants()->parse((string)$value);
        });
    },
    'split' => function ($separator = ',') {
        return Str::split((string)$this->value(), $separator);
    },
    'upper' => function () {
        return $this->value(function($value) {
            return Str::upper($value);
        });
    },
    'widont' => function () {

    },
    'words' => function () {

    },
    'xml' => function () {

    }
]);
