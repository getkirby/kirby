<?php

use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

return [
	'extends' => 'tags',
	'props' => [
		/**
		 * If set to `all`, any type of input is accepted. If set to `options` only the predefined options are accepted as input.
		 */
		'accept' => function ($value = 'options') {
			return V::in($value, ['all', 'options']) ? $value : 'all';
		},
		/**
		 * Custom icon to replace the arrow down.
		 */
		'icon' => function (string $icon = 'checklist') {
			return $icon;
		},
	],
	'methods' => [
		'toValues' => function ($value) {
			if (is_null($value) === true) {
				return [];
			}

			if (is_array($value) === false) {
				$value = Str::split($value, $this->separator());
			}

			return $this->sanitizeOptions($value);
		}
	],
];
