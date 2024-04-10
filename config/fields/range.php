<?php

use Kirby\Toolkit\I18n;

return [
	'extends' => 'number',
	'props' => [
		/**
		 * Unset inherited props
		 */
		'placeholder' => null,

		/**
		 * The maximum value on the slider
		 */
		'max' => function (float $max = 100) {
			return $max;
		},
		/**
		 * Enables/disables the tooltip and set the before and after values
		 */
		'tooltip' => function ($tooltip = true) {
			if (is_array($tooltip) === true) {
				$after             = $tooltip['after'] ?? null;
				$before            = $tooltip['before'] ?? null;
				$tooltip['after']  = I18n::translate($after, $after);
				$tooltip['before'] = I18n::translate($before, $before);
			}

			return $tooltip;
		},
	]
];
