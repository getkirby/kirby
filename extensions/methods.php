<?php

use Kirby\Cms\App;
use Kirby\Fields\Field;
use Kirby\Toolkit\Str;

/**
 * Field method setup
 */
Field::method([
    'upper' => function () {
        return $this->value(function($value) {
            return Str::upper($value);
        });
    },
    'lower' => function () {
        return $this->value(function($value) {
            return Str::lower($value);
        });
    },
    'int' => function () {
        return intval($this->value());
    },
    'smartypants' => function () {
        return $this->value(function($value) {
            return App::instance()->smartypants()->parse((string)$value);
        });
    },
    'markdown' => function () {
        return $this->value(function($value) {
            return App::instance()->markdown()->parse((string)$value);
        });
    },
    'kirbytext' => function () {
        return $this->value(function($value) {
            return App::instance()->kirbytext()->parse((string)$value);
        })->markdown();
    }
]);
