<?php

use Kirby\Panel\Controller\Dialog\UsersPickerDialogController;

return [
	'mixins' => [
		'layout',
		'min',
		'picker'
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
		 * Default selected user(s) when a new page/file/user is created
		 */
		'default' => function (string|array|bool|null $default = null) {
			return $default;
		},

		'value' => function ($value = null) {
			return $this->toFormValues($value);
		},
	],
	'computed' => [
		'default' => function (): array {
			if ($this->default === false) {
				return [];
			}

			if (
				$this->default === true &&
				$user = $this->kirby()->user()
			) {
				return [$user->id()];
			}

			return $this->toFormValues($this->default);
		}
	],
	'methods' => [
		'getIdFromArray' => function (array $array) {
			return $array['uuid'] ?? $array['id'] ?? $array['email'] ?? null;
		},
		'toModel' => function (string $id) {
			return $this->kirby()->user($id);
		}
	],
	'api' => function () {
		return [
			[
				'pattern' => 'items',
				'method'  => 'GET',
				'action'  => fn () => $this->field()->itemsFromRequest()
			],
		];
	},
	'dialogs' => fn () =>  [
		'picker' => fn () => new UsersPickerDialogController(...[
			'model'     => $this->model(),
			'hasSearch' => $this->search,
			'image'     => $this->image,
			'info'      => $this->info ?? false,
			'limit'     => $this->limit,
			'max'       => $this->max,
			'multiple'  => $this->multiple,
			'query'     => $this->query,
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
