<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

return [
	'mixins' => ['min', 'options'],
	'props' => [

		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'before'      => null,
		'placeholder' => null,

		/**
		 * If set to `all`, any type of input is accepted. If set to `options` only the predefined options are accepted as input.
		 */
		'accept' => function ($value = 'all') {
			return V::in($value, ['all', 'options']) ? $value : 'all';
		},
		/**
		 * Changes the tag icon
		 */
		'icon' => function ($icon = 'tag') {
			return $icon;
		},
		/**
		 * Set to `list` to display each tag with 100% width,
		 * otherwise the tags are displayed inline
		 */
		'layout' => function (string|null $layout = null) {
			return $layout;
		},
		/**
		 * Minimum number of required entries/tags
		 */
		'min' => function (int $min = null) {
			return $min;
		},
		/**
		 * Maximum number of allowed entries/tags
		 */
		'max' => function (int $max = null) {
			return $max;
		},
		/**
		 * Custom tags separator, which will be used to store tags in the content file
		 */
		'separator' => function (string $separator = ',') {
			return $separator;
		},
	],
	'computed' => [
		'default' => function (): array {
			return $this->toValues($this->default);
		},
		'value' => function (): array {
			return $this->toValues($this->value);
		}
	],
	'methods' => [
		'toValues' => function ($value) {
			if (is_null($value) === true) {
				return [];
			}

			if (is_array($value) === false) {
				$value = Str::split($value, $this->separator());
			}

			if ($this->accept === 'options') {
				$value = $this->sanitizeOptions($value);
			}

			return $value;
		}
	],
	'save' => function (array $value = null): string {
		return A::join(
			$value,
			$this->separator() . ' '
		);
	},
	'validations' => [
		'min',
		'max'
	]
];
