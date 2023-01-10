<?php


return [
	'extends' => 'text',
	'props' => [
		/**
		 * Unset inherited props
		 */
		'converter'  => null,
		'counter'    => null,
		'spellcheck' => null,

		/**
		 * Set of characters allowed in the slug
		 */
		'allow' => function (string $allow = '') {
			return $allow;
		},

		/**
		 * Changes the link icon
		 */
		'icon' => function (string $icon = 'url') {
			return $icon;
		},

		/**
		 * Set prefix for the help text
		 */
		'path'  => function (string $path = null) {
			return $path;
		},

		/**
		 * Name of another field that should be used to
		 * automatically update this field's value
		 */
		'sync'  => function (string $sync = null) {
			return $sync;
		},

		/**
		 * Set to object with keys `field` and `text` to add
		 * button to generate from another field
		 */
		'wizard' => function ($wizard = false) {
			return $wizard;
		}
	],
	'validations' => [
		'minlength',
		'maxlength'
	],
];
