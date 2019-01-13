<?php

use Kirby\Cms\App;
use Kirby\Cms\Field;
use Kirby\Cms\File;
use Kirby\Cms\Files;
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

        /**
         * Converts the field value into a proper boolean and inverts it
         *
         * @param Field $field
         * @return boolean
         */
        'isFalse' => function (Field $field): bool {
            return $field->toBool() === false;
        },

        /**
         * Converts the field value into a proper boolean
         *
         * @param Field $field
         * @return boolean
         */
        'isTrue' => function (Field $field): bool {
            return $field->toBool() === true;
        },

        /**
         * Validates the field content with the given validator and parameters
         *
         * @param string $validator
         * @param mixed[] ...$arguments A list of optional validator arguments
         * @return boolean
         */
        'isValid' => function (Field $field, string $validator, ...$arguments): bool {
            return V::$validator($field->value, ...$arguments);
        },

        // converters

        /**
         * Parses the field value with the given method
         *
         * @param Field $field
         * @param string $method [',', 'yaml', 'json']
         * @return array
         */
        'toData' => function (Field $field, string $method = ',') {
            switch ($method) {
                case 'yaml':
                    return Yaml::decode($field->value);
                case 'json':
                    return Json::decode($field->value);
                default:
                    return $field->split($method);
            }
        },

        /**
         * Converts the field value into a proper boolean
         *
         * @param Field $field
         * @param bool $default Default value if the field is empty
         * @return bool
         */
        'toBool' => function (Field $field, $default = false): bool {
            $value = $field->isEmpty() ? $default : $field->value;
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        },

        /**
         * Converts the field value to a timestamp or a formatted date
         *
         * @param Field $field
         * @param string $format PHP date formatting string
         * @return string|int
         */
        'toDate' => function (Field $field, string $format = null) use ($app) {
            if (empty($field->value) === true) {
                return null;
            }

            if ($format === null) {
                return $field->toTimestamp();
            }

            return $app->option('date.handler', 'date')($format, $field->toTimestamp());
        },

        /**
         * Returns a file object from a filename in the field
         *
         * @param Field $field
         * @return File|null
         */
        'toFile' => function (Field $field) {
            return $field->toFiles()->first();
        },

        /**
         * Returns a file collection from a yaml list of filenames in the field
         *
         * @return Files
         */
        'toFiles' => function (Field $field, string $separator = 'yaml') {
            $parent = $field->parent();
            $files  = new Files([]);

            foreach ($field->toData($separator) as $id) {
                if ($file = $parent->kirby()->file($id, $parent)) {
                    $files->add($file);
                }
            }

            return $files;
        },

        /**
         * Converts the field value into a proper float
         *
         * @param Field $field
         * @param float $default Default value if the field is empty
         * @return float
         */
        'toFloat' => function (Field $field, float $default = 0) {
            $value = $field->isEmpty() ? $default : $field->value;
            return floatval($value);
        },

        /**
         * Converts the field value into a proper integer
         *
         * @param Field $field
         * @param int $default Default value if the field is empty
         * @return int
         */
        'toInt' => function (Field $field, int $default = 0) {
            $value = $field->isEmpty() ? $default : $field->value;
            return intval($value);
        },

        /**
         * Wraps a link tag around the field value. The field value is used as the link text
         *
         * @param Field $field
         * @param mixed $attr1 Can be an optional Url. If no Url is set, the Url of the Page, File or Site will be used. Can also be an array of link attributes
         * @param mixed $attr2 If `$attr1` is used to set the Url, you can use `$attr2` to pass an array of additional attributes.
         * @return string
         */
        'toLink' => function (Field $field, $attr1 = null, $attr2 = null) {
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

        /**
         * Returns a page object from a page id in the field
         *
         * @param Field $field
         * @return Page|null
         */
        'toPage' => function (Field $field) use ($app) {
            return $field->toPages()->first();
        },

        /**
         * Returns a pages collection from a yaml list of page ids in the field
         *
         * @param string $separator Can be any other separator to split the field value by
         * @return Pages
         */
        'toPages' => function (Field $field, string $separator = 'yaml') use ($app) {
            return $app->site()->find(false, false, ...$field->toData($separator));
        },

        /**
         * Converts a yaml field to a Structure object
         */
        'toStructure' => function (Field $field) {
            return new Structure(Yaml::decode($field->value), $field->parent());
        },

        /**
         * Converts the field value to a Unix timestamp
         */
        'toTimestamp' => function (Field $field) {
            return strtotime($field->value);
        },

        /**
         * Turns the field value into an absolute Url
         */
        'toUrl' => function (Field $field) {
            return Url::to($field->value);
        },

        /**
         * Converts a user email address to a user object
         *
         * @return User|null
         */
        'toUser' => function (Field $field) use ($app) {
            return $field->toUsers()->first();
        },

        /**
         * Returns a users collection from a yaml list of user email addresses in the field
         *
         * @return Users
         */
        'toUsers' => function (Field $field, string $separator = 'yaml') use ($app) {
            return $app->users()->find(false, false, ...$field->toData($separator));
        },

        // inspectors

        /**
         * Returns the length of the field content
         */
        'length' => function (Field $field) {
            return Str::length($field->value);
        },

        /**
         * Returns the number of words in the text
         */
        'words' => function (Field $field) {
            return str_word_count(strip_tags($field->value));
        },

        // manipulators

        /**
         * Escapes the field value to be safely used in HTML
         * templates without the risk of XSS attacks
         *
         * @param Field $field
         * @param string $context html, attr, js or css
         */
        'escape' => function (Field $field, string $context = 'html') {
            $field->value = esc($field->value, $context);
            return $field;
        },

        /**
         * Creates an excerpt of the field value without html
         * or any other formatting.
         */
        'excerpt' => function (Field $field, int $chars = 0, bool $strip = true, string $rep = '…') {
            $field->value = Str::excerpt($field->kirbytext()->value(), $chars, $strip, $rep);
            return $field;
        },

        /**
         * Converts the field content to valid HTML
         */
        'html' => function (Field $field) {
            $field->value = htmlentities($field->value, ENT_COMPAT, 'utf-8');
            return $field;
        },

        /**
         * Converts the field content from Markdown/Kirbytext to valid HTML
         */
        'kirbytext' => function (Field $field) use ($app) {
            $field->value = $app->kirbytext($field->value, [
                'parent' => $field->parent(),
                'field'  => $field
            ]);

            return $field;
        },

        /**
         * Parses all KirbyTags without also parsing Markdown
         */
        'kirbytags' => function (Field $field) use ($app) {
            $field->value = $app->kirbytags($field->value, [
                'parent' => $field->parent(),
                'field'  => $field
            ]);

            return $field;
        },

        /**
         * Converts the field content to lowercase
         */
        'lower' => function (Field $field) {
            $field->value = Str::lower($field->value);
            return $field;
        },

        /**
         * Converts markdown to valid HTML
         */
        'markdown' => function (Field $field) use ($app) {
            $field->value = $app->markdown($field->value);
            return $field;
        },

        /**
         * Converts the field content to valid XML
         */
        'xml' => function (Field $field) {
            $field->value = Xml::encode($field->value);
            return $field;
        },

        /**
         * Cuts the string after the given length and adds "…" if it is longer
         *
         * @param int $length The number of characters in the string
         * @param string $appendix An optional replacement for the missing rest
         * @return Field
         */
        'short' => function (Field $field, int $length, string $appendix = '…') {
            $field->value = Str::short($field->value, $length, $appendix);
            return $field;
        },

        /**
         * Converts the field content to a slug
         */
        'slug' => function (Field $field) {
            $field->value = Str::slug($field->value);
            return $field;
        },

        /**
         * Applies SmartyPants to the field
         */
        'smartypants' => function (Field $field) use ($app) {
            $field->value = $app->smartypants($field->value);
            return $field;
        },

        /**
         * Splits the field content into an array
         */
        'split' => function (Field $field, $separator = ',') {
            return Str::split((string)$field->value, $separator);
        },

        /**
         * Converts the field content to uppercase
         */
        'upper' => function (Field $field) {
            $field->value = Str::upper($field->value);
            return $field;
        },

        /**
         * Avoids typographical widows in strings by replacing the last space with &nbsp;
         */
        'widont' => function (Field $field) {
            $field->value = Str::widont($field->value);
            return $field;
        },

        // aliases

        /**
         * Parses yaml in the field content and returns an array
         */
        'yaml' => function (Field $field): array {
            return $field->toData('yaml');
        },

    ];
};
