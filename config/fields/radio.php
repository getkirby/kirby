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
		 * Arranges the radio buttons in the given number of columns
		 */
		'columns' => function (int $columns = 1) {
			return $columns;
		},
		/**
		 * A radio button can be deactivated on click. If reset is `false` deactivating a radio button is no longer possible.
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
		}
	]
];
