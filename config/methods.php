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
use Kirby\Toolkit\Xml;

/**
 * Field method setup
 */
return function (App $app) {

    return [

        // states
        'isFalse' => function ($field) {
            return $field->toBool() === false;
        },
        'isTrue' => function ($field) {
            return $field->toBool() === true;
        },
        'isValid' => function ($field, $validator, ...$arguments) {
            return V::$validator($field->value, ...$arguments);
        },

        // converters
        'toData' => function ($field, $method = ',') {
            switch ($method) {
                case 'yaml':
                    return Yaml::decode($field->value);
                case 'json':
                    return Json::decode($field->value);
                default:
                    return $field->split($method);
            }
        },
        'toBool' => function ($field, $default = false) {
            $value = $field->isEmpty() ? $default : $field->value;
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        },
        'toDate' => function ($field, $format = null) {
            if (empty($field->value) === false) {
                return $format === null ? $field->toTimestamp() : date($format, $field->toTimestamp());
            }

            return null;
        },
        'toFile' => function ($field) {
            return $field->parent()->file($field->value);
        },
        'toFloat' => function ($field, $default = 0) {
            $value = $field->isEmpty() ? $default : $field->value;
            return floatval($value);
        },
        'toInt' => function ($field, $default = 0) {
            $value = $field->isEmpty() ? $default : $field->value;
            return intval($value);
        },
        'toLink' => function ($field, $attr1 = null, $attr2 = null) {
            if (is_string($attr1) === true) {
                $href = $attr1;
                $attr = $attr2;
            } else {
                $href = $field->parent()->url();
                $attr = $attr1;
            }

            if ($field->parent()->isActive()) {
                $attr['aria-current'] = 'page';
            }

            return Html::a($href, $field->value, $attr ?? []);
        },
        'toPage' => function ($field) use ($app) {
            return $app->site()->find($field->value);
        },
        'toPages' => function ($field, string $separator = 'yaml') use ($app) {
            return $app->site()->find(...$field->toData('yaml'));
        },
        'toStructure' => function ($field) {
            return new Structure(Yaml::decode($field->value), $field->parent());
        },
        'toTimestamp' => function ($field) {
            return strtotime($field->value);
        },
        'toUrl' => function ($field) {
            return Url::to($field->value);
        },
        'toUser' => function ($field) use ($app) {
            return $app->users()->find($field->value);
        },

        // inspectors
        'length' => function ($field) {
            return Str::length($field->value);
        },
        'words' => function ($field) {
            return str_word_count(strip_tags($field->value));
        },

        // manipulators
        'excerpt' => function ($field, int $chars = 0, bool $strip = true, string $rep = '…') {
            $field->value = Str::excerpt($field->value, $chars, $strip, $rep);
            return $field;
        },
        'html' => function ($field) {
            $field->value = htmlentities($field->value, ENT_COMPAT, 'utf-8');
            return $field;
        },
        'kirbytext' => function ($field) use ($app) {
            $field->value = $app->kirbytext($field->value, [
                'parent' => $field->parent(),
                'field'  => $field
            ]);

            return $field;
        },
        'kirbytags' => function ($field) use ($app) {
            $field->value = $app->kirbytags($field->value, [
                'parent' => $field->parent(),
                'field'  => $field
            ]);

            return $field;
        },
        'lower' => function ($field) {
            $field->value = Str::lower($field->value);
            return $field;
        },
        'markdown' => function ($field) use ($app) {
            $field->value = $app->markdown($field->value);
            return $field;
        },
        'xml' => function ($field) {
            $field->value = Xml::encode($field->value);
            return $field;
        },

        /**
         * @param int $length The number of characters in the string
         * @param string $appendix An optional replacement for the missing rest
         * @return Kirby\Cms\Field
         */
        'short' => function ($field, int $length, string $appendix = '…') {
            $field->value = Str::short($field->value, $length, $appendix);
            return $field;
        },
        'slug' => function () {
            $field->value = Str::slug($field->value);
            return $field;
        },
        'smartypants' => function ($field) use ($app) {
            $field->value = $app->smartypants($field->value);
            return $field;
        },
        'split' => function ($field, $separator = ',') {
            return Str::split((string)$field->value, $separator);
        },
        'upper' => function ($field) {
            $field->value = Str::upper($field->value);
            return $field;
        },
        'widont' => function ($field) {
            $field->value = Str::widont($field->value);
            return $field;
        },

        // aliases
        'yaml' => function ($field) {
            return $field->toData('yaml');
        },

    ];

};
