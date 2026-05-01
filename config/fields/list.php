<?php

use Kirby\Sane\Sane;

return [
	'props' => [
		/**
		 * Sets the allowed HTML formats. Available formats: `bold`, `italic`, `underline`, `strike`, `code`, `link`. Activate them all by passing `true`. Deactivate them all by passing `false`
		 */
		'marks' => function ($marks = true) {
			return $marks;
		},
		/**
		 * Sets the allowed nodes. Available nodes: `bulletList`, `orderedList`
		 */
		'nodes' => function ($nodes = null) {
			return $nodes;
		}
	],
	'computed' => [
		'value' => function () {
			$value = trim($this->value ?? '');
			$value = Sane::sanitizeProseMirrorFields($value);
			return $value;
		}
	]
];
