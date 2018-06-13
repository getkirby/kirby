<?php

use Kirby\Cms\App;
use Kirby\Cms\Field;
use Kirby\Cms\Html;
use Kirby\Cms\Structure;
use Kirby\Cms\Page;
use Kirby\Cms\Url;
use Kirby\Data\Json;
use Kirby\Data\Yaml;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

/**
 * Field method setup
 */
return function (App $app) {

    return [

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
        'isValid' => function ($validator, ...$arguments) {
            return V::$validator($this->value(), ...$arguments);
        },

        // converters
        'toData' => function ($method = ',') {
            switch ($method) {
                case 'yaml':
                    return Yaml::decode($this->value());
                case 'json':
                    return Json::decode($this->value());
                default:
                    return $this->split($method);
            }
        },
        'toBool' => function ($default = false) {
            $value = $this->isEmpty() ? $default : $this->value();
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        },
        'toDate' => function ($format = null) {
            if (empty($this->value()) === false) {
                return $format === null ? $this->toTimestamp() : date($format, $this->toTimestamp());
            }

            return null;
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
            if (is_string($attr1) === true) {
                $href = $attr1;
                $attr = $attr2;
            } else {
                $href = $this->parent()->url();
                $attr = $attr1;
            }

            if ($this->parent()->isActive()) {
                $attr['aria-current'] = 'page';
            }

            return Html::a($href, $this->value(), $attr ?? []);
        },
        'toPage' => function () use ($app) {
            return $app->site()->find($this->value());
        },
        'toPages' => function (string $separator = 'yaml') use ($app) {
            return $app->site()->find(...$this->toData('yaml'));
        },
        'toStructure' => function () {
            return new Structure(Yaml::decode($this->value()), $this->parent());
        },
        'toTimestamp' => function () {
            return strtotime($this->value());
        },
        'toUrl' => function () {
            return Url::to($this->value());
        },
        'toUser' => function () use ($app) {
            return $app->users()->find($this->value());
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
            return $this->value(function ($value) {
                return htmlentities($value, ENT_COMPAT, 'utf-8');
            });
        },
        'kirbytext' => function () use ($app) {
            return $this->value(function ($value) use ($app) {
                return $app->kirbytext($value, [
                    'parent' => $this->parent(),
                    'field'  => $this
                ]);
            });
        },
        'kirbytags' => function () use ($app) {
            return $this->value(function ($value) use ($app) {
                return $app->kirbytags($value, [
                    'parent' => $this->parent(),
                    'field'  => $this
                ]);
            });
        },
        'lower' => function () {
            return $this->value(function ($value) {
                return Str::lower($value);
            });
        },
        'markdown' => function () use ($app) {
            return $this->value(function ($value) use ($app) {
                return $app->markdown($value);
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

        /**
         * @param int $length The number of characters in the string
         * @param string $appendix An optional replacement for the missing rest
         * @return Kirby\Cms\Field
         */
        'short' => function (int $length, string $appendix = '…') {
            return $this->value(function ($value) use ($length, $appendix) {
                return Str::short($this->value(), $length, $appendix);
            });
        },
        'slug' => function () {
            return $this->value(function ($value) {
                return Str::slug($value);
            });
        },
        'smartypants' => function () use ($app) {
            return $this->value(function ($value) use ($app) {
                return $app->smartypants()->parse((string)$value);
            });
        },
        'split' => function ($separator = ',') {
            return Str::split((string)$this->value(), $separator);
        },
        'upper' => function () {
            return $this->value(function ($value) {
                return Str::upper($value);
            });
        },
        'widont' => function () {
            return $this->value(function ($value) {
                return Str::widont($value);
            });
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
        'link' => function (...$attributes) {
            return $this->toLink(...$attributes);
        },
        'md' => function () {
            return $this->markdown();
        },
        'sp' => function () {
            return $this->smartypants();
        },
        'v' => function (...$arguments) {
            return $this->isValid(...$arguments);
        },
        'yaml' => function () {
            return $this->toData('yaml');
        },
        'x' => function () {
            return $this->xml();
        }

    ];

};
