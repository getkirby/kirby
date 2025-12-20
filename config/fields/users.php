<?php

return [
	'mixins' => [
		'layout',
		'min',
		'picker',
		'userpicker'
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
				return [$this->toItem($user)];
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
				'pattern' => '/',
				'action' => function () {
					$field = $this->field();

					return $field->userpicker([
						'image'  => $field->image(),
						'info'   => $field->info(),
						'layout' => $field->layout(),
						'limit'  => $field->limit(),
						'page'   => $this->requestQuery('page'),
						'query'  => $field->query(),
						'search' => $this->requestQuery('search'),
						'text'   => $field->text()
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
