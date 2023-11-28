<?php

use Kirby\Field\FieldOptions;

return [
	'props' => [
		/**
		 * API settings for options requests. This will only take affect when `options` is set to `api`.
		 */
		'api' => function ($api = null) {
			return $api;
		},
		/**
		 * An array with options
		 */
		'options' => function ($options = []) {
			return $options;
		},
		/**
		 * Query settings for options queries. This will only take affect when `options` is set to `query`.
		 */
		'query' => function ($query = null) {
			return $query;
		},
	],
	'computed' => [
		'options' => function (): array {
			return $this->getOptions();
		}
	],
	'methods' => [
		'getOptions' => function () {
			$props   = FieldOptions::polyfill($this->props);
			$options = FieldOptions::factory($props['options']);
			return $options->render($this->model());
		},
		'sanitizeOption' => function ($value) {
			$options = array_column($this->options(), 'value');
			return in_array($value, $options) === true ? $value : null;
		},
		'sanitizeOptions' => function ($values) {
			$options = array_column($this->options(), 'value');
			$options = array_intersect($values, $options);
			return array_values($options);
		},
	]
];
