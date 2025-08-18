<?php

use Kirby\Toolkit\A;

return [
	'mixins' => [
		'layout',
		'min',
		'pagepicker',
		'picker',
	],
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'autofocus'   => null,
		'before'      => null,
		'icon'        => null,
		'placeholder' => null,

		/**
		 * Default selected page(s) when a new page/file/user is created
		 */
		'default' => function ($default = null) {
			return $this->toFormValues($default);
		},

		/**
		 * Optional query to select a specific set of pages
		 */
		'query' => function (string|null $query = null) {
			return $query;
		},

		/**
		 * Optionally include subpages of pages
		 */
		'subpages' => function (bool $subpages = true) {
			return $subpages;
		},

		'value' => function ($value = null) {
			return $this->toFormValues($value);
		},
	],
	'computed' => [
		/**
		 * Unset inherited computed
		 */
		'default' => null
	],
	'methods' => [
		'toModel' => function (string $id) {
			return $this->kirby()->page($id);
		}
	],
	'api' => function () {
		return [
			[
				'pattern' => '/',
				'action' => function () {
					$field = $this->field();

					return $field->pagepicker([
						'image'    => $field->image(),
						'info'     => $field->info(),
						'layout'   => $field->layout(),
						'limit'    => $field->limit(),
						'page'     => $this->requestQuery('page'),
						'parent'   => $this->requestQuery('parent'),
						'query'    => $field->query(),
						'search'   => $this->requestQuery('search'),
						'subpages' => $field->subpages(),
						'text'     => $field->text()
					]);
				}
			]
		];
	},
	'save' => function ($value = null) {
		return $this->toStoredValues($value);
	},
	'validations' => [
		'max',
		'min'
	]
];
