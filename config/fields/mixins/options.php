<?php

use Kirby\Field\FieldOptions;
use Kirby\Toolkit\Str;

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
			// make sure to flush the options cache when
			// new options are being passed
			$this->optionsCache = null;
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
			$options = $this->optionsCache ??= $this->getOptions();

			// values missing from the options might belong to models the
			// current user must not list; keep those as placeholders
			// to not drop them from the value on save
			$split = fn ($value) => match (true) {
				is_array($value) => $value,
				is_string($value) && $this->separator !== null
					=> Str::split($value, $this->separator),
				default => [$value]
			};

			$keep = array_diff(
				[...$split($this->value), ...$split($this->default)],
				array_column($options, 'value'),
				['']
			);

			if ($keep === []) {
				return $options;
			}

			return $this->getOptions(array_values($keep));
		}
	],
	'methods' => [
		'getOptions' => function (array $keep = []) {
			$props   = FieldOptions::polyfill($this->props);
			$options = FieldOptions::factory($props['options']);
			return $options->render($this->model(), $keep);
		},
		'sanitizeOption' => function ($value) {
			$options = array_column($this->options(), 'value');
			return in_array($value, $options) ? $value : null;
		},
		'sanitizeOptions' => function ($values) {
			$options = array_column($this->options(), 'value');
			$options = array_intersect($values, $options);
			return array_values($options);
		},
	]
];
