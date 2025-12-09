<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
	'mixins' => ['min', 'options'],
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'before'      => null,
		'icon'        => null,
		'placeholder' => null,

		/**
		 * Show/hide the batch select toggle
		 */
		'batch' => function (bool $batch = false) {
			return $batch;
		},

		/**
		 * Arranges the checkboxes in the given number of columns
		 */
		'columns' => function (int $columns = 1) {
			return $columns;
		},
		/**
		 * Default value for the field, which will be used when a page/file/user is created
		 */
		'default' => function ($default = null) {
			return Str::split($default, ',');
		},
		/**
		 * Maximum number of checked boxes
		 */
		'max' => function (int|null $max = null) {
			return $max;
		},
		/**
		 * Minimum number of checked boxes
		 */
		'min' => function (int|null $min = null) {
			return $min;
		},
		'value' => function ($value = null) {
			return Str::split($value, ',');
		},
	],
	'computed' => [
		'default' => function () {
			return $this->sanitizeOptions($this->default);
		},
		'value' => function () {
			return $this->sanitizeOptions($this->value);
		},
	],
	'methods' => [
		'emptyValue' => function () {
			return [];
		}
	],
	'save' => function ($value): string {
		return A::join($value, ', ');
	},
	'validations' => [
		'options',
		'max',
		'min'
	]
];
