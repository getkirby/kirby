<?php

use Kirby\Panel\Controller\Dialog\PagesPickerDialogController;

return [
	'mixins' => [
		'layout',
		'min',
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
				'pattern' => 'items',
				'method'  => 'GET',
				'action'  => fn () => $this->field()->itemsFromRequest()
			]
		];
	},
	'dialogs' => fn () =>  [
		'picker' => fn () => new PagesPickerDialogController(...[
			'model'     => $this->model(),
			'hasSearch' => $this->search,
			'image'     => $this->image,
			'info'      => $this->info ?? false,
			'limit'     => $this->limit,
			'max'       => $this->max,
			'multiple'  => $this->multiple,
			'query'     => $this->query,
			'subpages'  => $this->subpages,
			'text'      => $this->text,
			...$this->picker
		])
	],
	'save' => function ($value = null) {
		return $this->toStoredValues($value);
	},
	'validations' => [
		'max',
		'min'
	]
];
