<?php

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Panel\UsersPicker;
use Kirby\Toolkit\A;

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
					$this->toUser($user)
				];
			}

			return $this->toUsers($this->default);
		}
	],
	'methods' => [
		'toUser' => function (User $user): array {
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

			foreach (Data::decode($value, 'yaml') as $email) {
				if (is_array($email) === true) {
					$email = $email['email'] ?? null;
				}

				if ($email !== null && ($user = $kirby->user($email))) {
					$users[] = $this->toUser($user);
				}
			}

			return $users;
		}
	],
	'api' => function (): array {
		return [
			[
				'pattern' => '/',
				'action' => function (): array {
					$field  = $this->field();
					$picker = new UsersPicker([
						'image'  => $field->image(),
						'info'   => $field->info(),
						'layout' => $field->layout(),
						'limit'  => $field->limit(),
						'model'  => $field->model(),
						'page'   => $this->requestQuery('page'),
						'query'  => $field->query(),
						'search' => $this->requestQuery('search'),
						'text'   => $field->text()
					]);

					return $picker->toArray();
				}
			]
		];
	},
	'save' => function ($value = null): array {
		return A::pluck($value, $this->store);
	},
	'validations' => [
		'max',
		'min'
	]
];
