<?php

return [
	'mixins' => ['options'],
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'before'      => null,
		'icon'        => null,
		'placeholder' => null,

		/**
		 * Toggles will automatically span the full width of the field. With the grow option, you can disable this behaviour for a more compact layout.
		 */
		'grow' => function (bool $grow = true) {
			return $grow;
		},
		/**
		 * If `false` all labels will be hidden for icon-only toggles.
		 */
		'labels' => function (bool $labels = true) {
			return $labels;
		},
		/**
		 * A toggle can be deactivated on click. If reset is `false` deactivating a toggle is no longer possible.
		 */
		'reset' => function (bool $reset = true) {
			return $reset;
		}
	],
	'computed' => [
		'default' => function () {
			return $this->sanitizeOption($this->default);
		},
		'value' => function () {
			return $this->sanitizeOption($this->value) ?? '';
		},
	]
];
