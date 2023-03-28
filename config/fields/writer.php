<?php

use Kirby\Sane\Sane;

return [
	'props' => [
		/**
		 * Available heading levels
		 */
		'headings' => function (array|null $headings = null) {
			return array_intersect($headings ?? range(1, 6), range(1, 6));
		},
		/**
		 * Enables inline mode, which will not wrap new lines in paragraphs and creates hard breaks instead.
		 *
		 * @param bool $inline
		 */
		'inline' => function (bool $inline = false) {
			return $inline;
		},
		/**
		 * Sets the allowed HTML formats. Available formats: `bold`, `italic`, `underline`, `strike`, `code`, `link`, `email`. Activate/deactivate them all by passing `true`/`false`. Default marks are `bold`, `italic`, `underline`, `strike`, `link`, `email`
		 * @param array|bool $marks
		 */
		'marks' => function ($marks = null) {
			return $marks;
		},
		/**
		 * Sets the allowed nodes. Available nodes: `paragraph`, `heading`, `bulletList`, `orderedList`. Activate/deactivate them all by passing `true`/`false`. Default nodes are `paragraph`, `heading`, `bulletList`, `orderedList`.
		 * @param array|bool|null $nodes
		 */
		'nodes' => function ($nodes = null) {
			return $nodes;
		},
		/**
		 * Toolbar options, incl. `marks` (to narrow down which marks should have toolbar buttons), `nodes` (to narrow down which nodes should have toolbar dropdown entries) and `inline` to set the position of the toolbar (false = sticking on top of the field)
		 */
		'toolbar' => function ($toolbar = null) {
			return $toolbar;
		}
	],
	'computed' => [
		'toolbar' => function () {
			return array_merge([
				'inline' => true,
				'marks'  => $this->marks ?? [
					'bold',
					'italic',
					'underline',
					'strike',
					'code',
					'|',
					'link',
					'email',
					'|',
					'clear'
				]
			], $this->toolbar ?? []);
		},
		'value' => function () {
			$value = trim($this->value ?? '');
			return Sane::sanitize($value, 'html');
		}
	],
];
