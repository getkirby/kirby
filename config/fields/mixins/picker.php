<?php

use Kirby\Data\Yaml;
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
        'default' => function ($default = null) {
            return $default;
        },

        /**
         * The placeholder text if none have been selected yet
         */
        'empty' => function ($empty = null) {
            return I18n::translate($empty, $empty);
        },

        /**
         * Info text for each item
         */
        'info' => function (string $info = null) {
            return $info;
        },

        /**
         * Changes the layout of the selected files. Available layouts: `list`, `cards`
         */
        'layout' => function (string $layout = 'list') {
            return $layout;
        },

        /**
         * Whether each item should be clickable
         */
        'link' => function (bool $link = true) {
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

        'value' => function ($value = null) {
            return $value;
        }

    ],
    'computed' => [
        'default' => function () {
            return Yaml::decode($this->default);
        },
        'value' => function () {
            return Yaml::decode($this->value);
        },
    ],
    'methods' => [
        'modelResponse' => function ($model) {
            return $model->panelPickerData([
                'info'    => $this->info ?? false,
                'model'   => $this->model(),
                'preview' => $this->preview,
                'text'    => $this->text,
            ]);
        },
        'toModel' => function ($id = null) {
            return $id;
        },
        'toModels' => function ($ids = [], Closure $callback) {
            $models = array_map(function ($id) {
                if ($model = $this->toModel($id)) {
                    return $this->modelResponse($model);
                }
            }, $ids);
            $models = array_filter($ids);
            return $models;
        }
    ],
    'validations' => [
        'max',
        'min'
    ]
];
