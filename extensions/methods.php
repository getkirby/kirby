<?php

use Kirby\Cms\App;
use Kirby\Cms\Field;
use Kirby\Cms\Structure;
use Kirby\Data\Handler\Json;
use Kirby\Data\Handler\Yaml;
use Kirby\Html\Element\A;
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
    'toArray' => function ($method):array {
        switch ($method) {
            case 'yaml':
                return Yaml::decode($this->value());
            case 'json':
                return Json::decode($this->value());
            default;
                return $this->split($method);
        }
    },
    'toBool' => function ($default = false) {
        $value = $this->isEmpty() ? $default : $this->value();
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    },
    'toDate' => function ($format = null) {
        return $format === null ? $this->toTimestamp() : date($format, $this->toTimestamp());
    },
    'toExcerpt' => function () {
        return $this;
    },
    'toFile' => function () {
        return $this->parent()->file($this->value());
    },
    'toFloat' => function ($default = 0) {
        $value = $this->isEmpty() ? $default : $this->value();
        return floatval($value);
    },
    'toInt' => function ($default = 0) {
        $value = $this->isEmpty() ? $default : $this->value();
        return intval($value);
    },
    'toLink' => function ($attr1 = null, $attr2 = null) {

        $a = new A($this->parent()->url(), $this->value());

        if (is_string($attr1) === true) {
            $a->attr('href', url($attr1));
            $a->attr($attr2);
        } else {
            $a->attr($attr1);
        }

        return $a;

    },
    'toPage' => function () {
        return App::instance()->site()->find($this->value());
    },
    'toPages' => function (string $separator = 'yaml') {
        return App::instance()->site()->find(...$this->toArray('yaml'));
    },
    'toStructure' => function () {
        return new Structure(Yaml::decode($this->value()), $this->parent());
    },
    'toTimestamp' => function () {
        return strtotime($this->value());
    },
    'toUrl' => function (): string {
        // TODO: solve this without using the helper
        return url($this->value());
    },
    'toUser' => function () {
        return App::instance()->users()->find($this->value());
    },

    // inspectors
    'length' => function () {
        return Str::length($this->value());
    },

    // manipulators
    'escape' => function () {
        throw new Exception('Not implemented yet');
    },
    'html' => function () {
        // TODO: test compatibility with old Html::encode
        return htmlentities($this->value(), ENT_COMPAT, 'utf-8');
    },
    'kirbytext' => function () {
        return $this->value(function($value) {
            return App::instance()->kirbytext()->parse((string)$value);
        })->markdown();
    },
    'kirbytags' => function () {
        return $this->value(function ($value) {
            return App::instance()->kirbytext()->parse((string)$value);
        });
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

        if ($this->isNotEmpty()) {
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
        throw new Exception('Not implemented yet');
    },
    'words' => function () {
        throw new Exception('Not implemented yet');
    },
    'xml' => function () {
        throw new Exception('Not implemented yet');
    },

    // Aliases
    'bool' => function () {
        return $this->toBool();
    },
    'esc' => function () {
        return $this->escape();
    },
    'excerpt' => function () {
        return $this->toExcerpt();
    },
    'float' => function () {
        return $this->toFloat();
    },
    'h' => function () {
        return $this->html();
    },
    'int' => function () {
        return $this->toInt();
    },
    'kt' => function () {
        return $this->kirbytext();
    },
    'link' => function () {
        return $this->toLink();
    },
    'md' => function () {
        return $this->markdown();
    },
    'sp' => function () {
        return $this->smartypants();
    },
    'yaml' => function () {
        return $this->toArray('yaml');
    },
    'x' => function () {
        return $this->xml();
    }

]);
