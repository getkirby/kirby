<?php

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Toolkit\A;

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
			return $this->toUsers($value);
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
				return [
					$this->userResponse($user)
				];
			}

			return $this->toUsers($this->default);
		}
	],
	'methods' => [
		'userResponse' => function ($user) {
			return $user->panel()->pickerData([
				'info'   => $this->info,
				'image'  => $this->image,
				'layout' => $this->layout,
				'text'   => $this->text,
			]);
		},
		'toUsers' => function ($value = null): array {
			$users = [];
			$kirby = App::instance();

			foreach (Data::decode($value, 'yaml') as $id) {
				if (is_array($id) === true) {
					$id =  $id['uuid'] ?? $id['id'] ?? $id['email'] ?? null;
				}

				if ($id !== null && ($user = $kirby->user($id))) {
					$users[] = $this->userResponse($user);
				}
			}

			return $users;
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
		return A::pluck($value, $this->store);
	},
	'validations' => [
		'max',
		'min'
	]
];
