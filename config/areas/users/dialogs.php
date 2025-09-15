<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Cms\UserRules;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Field;
use Kirby\Panel\Panel;
use Kirby\Panel\UserTotpDisableDialog;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

$fields = require __DIR__ . '/../fields/dialogs.php';
$files = require __DIR__ . '/../files/dialogs.php';

return [
	'user.create' => [
		'pattern' => 'users/create',
		'load' => function () {
			$kirby = App::instance();
			$roles = $kirby->roles()->canBeCreated();

			// get default value for role
			if ($role = $kirby->request()->get('role')) {
				$role = $roles->find($role)?->id();
			}

			// get role field definition, incl. available role options
			$roles = Field::role(
				roles: $roles,
				props: ['required' => true]
			);

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'name'  => Field::username(),
						'email' => Field::email([
							'link'     => false,
							'required' => true
						]),
						'password'     => Field::password([
							'autocomplete' => 'new-password'
						]),
						'translation'  => Field::translation([
							'required' => true
						]),
						'role' => $roles
					],
					'submitButton' => I18n::translate('create'),
					'value' => [
						'name'        => '',
						'email'       => '',
						'password'    => '',
						'translation' => $kirby->panelLanguage(),
						'role'        => $role ?: $roles['options'][0]['value'] ?? null
					]
				]
			];
		},
		'submit' => function () {
			$kirby = App::instance();

			$kirby->users()->create([
				'name'     => $kirby->request()->get('name'),
				'email'    => $kirby->request()->get('email'),
				'password' => $kirby->request()->get('password'),
				'language' => $kirby->request()->get('translation'),
				'role'     => $kirby->request()->get('role')
			]);

			return [
				'event' => 'user.create'
			];
		}
	],

	'user.changeEmail' => [
		'pattern' => 'users/(:any)/changeEmail',
		'load' => function (string $id) {
			$user = Find::user($id);

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'email' => [
							'label'     => I18n::translate('email'),
							'required'  => true,
							'type'      => 'email',
							'preselect' => true
						]
					],
					'submitButton' => I18n::translate('change'),
					'value' => [
						'email' => $user->email()
					]
				]
			];
		},
		'submit' => function (string $id) {
			$request = App::instance()->request();

			Find::user($id)->changeEmail($request->get('email'));

			return [
				'event' => 'user.changeEmail'
			];
		}
	],

	'user.changeLanguage' => [
		'pattern' => 'users/(:any)/changeLanguage',
		'load' => function (string $id) {
			$user = Find::user($id);

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'translation' => Field::translation(['required' => true])
					],
					'submitButton' => I18n::translate('change'),
					'value' => [
						'translation' => $user->language()
					]
				]
			];
		},
		'submit' => function (string $id) {
			$request = App::instance()->request();

			Find::user($id)->changeLanguage($request->get('translation'));

			return [
				'event'  => 'user.changeLanguage',
				'reload' => [
					'globals' => '$translation'
				]
			];
		}
	],

	'user.changeName' => [
		'pattern' => 'users/(:any)/changeName',
		'load' => function (string $id) {
			$user = Find::user($id);

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'name' => Field::username([
							'preselect' => true
						])
					],
					'submitButton' => I18n::translate('rename'),
					'value' => [
						'name' => $user->name()->value()
					]
				]
			];
		},
		'submit' => function (string $id) {
			$request = App::instance()->request();

			Find::user($id)->changeName($request->get('name'));

			return [
				'event' => 'user.changeName'
			];
		}
	],

	'user.changePassword' => [
		'pattern' => 'users/(:any)/changePassword',
		'load' => function (string $id) {
			Find::user($id);

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields'       => [
						'currentPassword' => Field::password([
							'label'        => I18n::translate('user.changePassword.current'),
							'autocomplete' => 'current-password'
						]),
						'password' => Field::password([
							'label'        => I18n::translate('user.changePassword.new'),
							'autocomplete' => 'new-password'
						]),
						'passwordConfirmation' => Field::password([
							'label'        => I18n::translate('user.changePassword.new.confirm'),
							'autocomplete' => 'new-password'
						])
					],
					'submitButton' => I18n::translate('change'),
				]
			];
		},
		'submit' => function (string $id) {
			$kirby   = App::instance();
			$request = $kirby->request();

			$user                 = Find::user($id);
			$currentPassword      = $request->get('currentPassword');
			$password             = $request->get('password');
			$passwordConfirmation = $request->get('passwordConfirmation');

			// validate the current password of the acting user
			try {
				$kirby->user()->validatePassword($currentPassword);
			} catch (Exception) {
				// catching and re-throwing exception to avoid automatic
				// sign-out of current user from the Panel
				throw new InvalidArgumentException([
					'key' => 'user.password.wrong'
				]);
			}

			// validate the new password
			UserRules::validPassword($user, $password ?? '');

			// compare passwords
			if ($password !== $passwordConfirmation) {
				throw new InvalidArgumentException(
					key: 'user.password.notSame'
				);
			}

			// change password if everything's fine
			$user->changePassword($password);

			return [
				'event' => 'user.changePassword'
			];
		}
	],

	'user.changeRole' => [
		'pattern' => 'users/(:any)/changeRole',
		'load' => function (string $id) {
			$user = Find::user($id);

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'role' => Field::role(
							roles: $user->roles(),
							props: [
								'label'    => I18n::translate('user.changeRole.select'),
								'required' => true,
							]
						)
					],
					'submitButton' => I18n::translate('user.changeRole'),
					'value' => [
						'role' => $user->role()->name()
					]
				]
			];
		},
		'submit' => function (string $id) {
			$request = App::instance()->request();

			$user = Find::user($id)->changeRole($request->get('role'));

			return [
				'event' => 'user.changeRole',
				'user' => $user->toArray()
			];
		}
	],

	'user.delete' => [
		'pattern' => 'users/(:any)/delete',
		'load' => function (string $id) {
			$user       = Find::user($id);
			$i18nPrefix = $user->isLoggedIn() ? 'account' : 'user';

			return [
				'component' => 'k-remove-dialog',
				'props' => [
					'text' => I18n::template($i18nPrefix . '.delete.confirm', [
						'email' => Escape::html($user->email())
					])
				]
			];
		},
		'submit' => function (string $id) {
			$user     = Find::user($id);
			$redirect = false;
			$referrer = Panel::referrer();
			$url      = $user->panel()->url(true);

			$user->delete();

			// redirect to the users view
			// if the dialog has been opened in the user view
			if ($referrer === $url) {
				$redirect = '/users';
			}

			// logout the user if they deleted themselves
			if ($user->isLoggedIn()) {
				$redirect = '/logout';
			}

			return [
				'event'    => 'user.delete',
				'redirect' => $redirect
			];
		}
	],

	'user.fields' => [
		...$fields['model'],
		'pattern' => '(users/[^/]+)/fields/(:any)/(:all?)',
	],

	'user.file.changeName' => [
		...$files['changeName'],
		'pattern' => '(users/[^/]+)/files/(:any)/changeName',
	],

	'user.file.changeSort' => [
		...$files['changeSort'],
		'pattern' => '(users/[^/]+)/files/(:any)/changeSort',
	],

	'user.file.changeTemplate' => [
		...$files['changeTemplate'],
		'pattern' => '(users/[^/]+)/files/(:any)/changeTemplate',
	],

	'user.file.delete' => [
		...$files['delete'],
		'pattern' => '(users/[^/]+)/files/(:any)/delete',
	],

	'user.file.fields' => [
		...$fields['file'],
		'pattern' => '(users/[^/]+)/files/(:any)/fields/(:any)/(:all?)',
	],

	'user.totp.disable' => [
		'pattern' => 'users/(:any)/totp/disable',
		'load'    => fn (string $id) => (new UserTotpDisableDialog($id))->load(),
		'submit'  => fn (string $id) => (new UserTotpDisableDialog($id))->submit()
	],
];
