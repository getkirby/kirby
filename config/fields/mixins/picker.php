<?php

use Kirby\Toolkit\I18n;

return [
	'props' => [
		/**
		 * The placeholder text if none have been selected yet
		 */
		'empty' => function ($empty = null) {
			return I18n::translate($empty, $empty);
		},

		/**
		 * Image settings for each item
		 */
		'image' => function ($image = null) {
			return $image;
		},

		/**
		 * Info text for each item
		 */
		'info' => function (string $info = null) {
			return $info;
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
		 * Main text for each item
		 */
		'text' => function (string $text = null) {
			return $text;
		},

	],
];
