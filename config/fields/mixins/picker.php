<?php

use Kirby\Data\Data;
use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * Unset inherited props
         */
        'after'       => null,
        'before'      => null,
        'autofocus'   => null,
        'icon'        => null,
        'placeholder' => null,

        /**
         * Sets the model(s), which are selected by default
         * when a new page/file/user is created
         */
        'default' => function ($default = []) {
            return $default;
        },

        /**
         * The placeholder text/icon if none have been selected yet
         */
        'empty' => function ($empty = null) {
            // TODO: handle cases where defined as text-icon array
            return I18n::translate($empty, $empty);
        },

        /**
         * Info text for each item
         */
        'info' => function (string $info = null) {
            return $info;
        },

        /**
         * Changes the layout of the selected files.
         * Available layouts: `list`, `cards`
         */
        'layout' => function (string $layout = 'list') {
            return $layout;
        },

        /**
         * Whether each item should be clickable
         */
        'link' => function (bool $link = true) {
            // TODO: the default value somehow gets ignored/
            // not passed to Vue component
            return $link;
        },

        /**
         * The minimum number of required selected
         */
        'min' => function (int $min = null) {
            return $min;
        },

        /**
         * The maximum number of allowed selected
         */
        'max' => function (int $max = null) {
            return $max;
        },

        /**
         * If `false`, only a single one can be selected
         */
        'multiple' => function (bool $multiple = true) {
            return $multiple;
        },

        /**
         * Query for the items to be included in the picker
         */
        'query' => function (string $query = null) {
            return $query;
        },

        /**
         * Enable/disable the search field in the picker
         */
        'search' => function (bool $search = true) {
            return $search;
        },

        /**
         * Layout size for cards: `tiny`, `small`, `medium`, `large` or `huge`
         */
        'size' => function (string $size = 'auto') {
            return $size;
        },

        /**
         * Main text for each item
         */
        'text' => function (string $text = null) {
            return $text;
        },

        'value' => function ($value = []) {
            return $value;
        }
    ],
    'computed' => [
        'default' => function (): array {
            return $this->toValue($this->default);
        },
        'value' => function (): array {
            return $this->toValue($this->value);
        },
    ],
    'methods' => [
        'modelResponse' => function ($model): array {
            return $model->panelPickerData([
                'info'    => $this->info ?? false,
                'model'   => $this->model(),
                'preview' => $this->preview,
                'text'    => $this->text,
            ]);
        },
        'toModels' => function (array $ids = []): array {
            $models = array_map(function ($id) {
                if ($model = $this->toModel($id)) {
                    return $this->modelResponse($model);
                }
            }, $ids);
            $models = array_filter($models);
            return $models;
        },
        'toValue' => function ($value): array {
            if (is_array($value) === true) {
                return $value;
            }

            return Data::decode($value, 'yaml');
        }
    ],
    'validations' => [
        'max',
        'min'
    ]
];
