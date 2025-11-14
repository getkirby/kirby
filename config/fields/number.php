<?php

use Kirby\Toolkit\Str;

return [
	'props' => [
		/**
		 * Default number that will be saved
		 * when a new page/user/file is created
		 */
		'default' => function ($default = null) {
			return $default;
		},
		/**
		 * The lowest allowed number
		 */
		'min' => function (float|null $min = null) {
			return $min;
		},
		/**
		 * The highest allowed number
		 */
		'max' => function (float|null $max = null) {
			return $max;
		},
		/**
		 * Allowed incremental steps between numbers (i.e `0.5`)
		 * Use `any` to allow any decimal value.
		 */
		'step' => function ($step = null): float|string {
			return match ($step) {
				'any'   => 'any',
				default => $this->toNumber($step) ?? $this->emptyValue()
			};
		},
		'value' => function ($value = null) {
			return $this->toNumber($value) ?? $this->emptyValue();
		}
	],
	'computed' => [
		'default' => function () {
			$default = $this->default;

			if (is_string($default) === true) {
				$default = $this->model()->toString($default);
			}

			return $this->toNumber($default) ?? $this->emptyValue();
		}
	],
	'methods' => [
		'emptyValue' => function () {
			return '';
		},
		'toNumber' => function ($value): float|null {
			if ($this->isEmptyValue($value) === true) {
				return null;
			}

			return is_float($value) === true ? $value : (float)Str::float($value);
		}
	],
	'validations' => [
		'min',
		'max'
	]
];
