<?php

use Kirby\Cms\Find;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use Kirby\Form\Form;

/**
 * User Routes
 */
return [
	// @codeCoverageIgnoreStart
	[
		'pattern' => 'users',
		'method'  => 'GET',
		'action'  => function () {
			return Find::users()->sort('username', 'asc', 'email', 'asc');
		}
	],
	[
		'pattern' => 'users',
		'method'  => 'POST',
		'action'  => function () {
			return Find::users()->create($this->request()->body()->toArray());
		}
	],
	[
		'pattern' => 'users/search',
		'method'  => 'GET|POST',
		'action'  => function () {
			return $this->search(Find::users());
		}
	],
	[
		'pattern' => [
			'(account)',
			'users/(:any)',
		],
		'method'  => 'GET',
		'action'  => function (string $id) {
			return Find::user($id);
		}
	],
	[
		'pattern' => [
			'(account)',
			'users/(:any)',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return Find::user($id)->update($this->request()->body()->toArray(), $this->language(), true);
		}
	],
	[
		'pattern' => [
			'(account)',
			'users/(:any)',
		],
		'method'  => 'DELETE',
		'action'  => function (string $id) {
			return Find::user($id)->delete();
		}
	],
	[
		'pattern' => [
			'(account)/avatar',
			'users/(:any)/avatar',
		],
		'method'  => 'GET',
		'action'  => function (string $id) {
			return Find::user($id)->avatar();
		}
	],
	// @codeCoverageIgnoreStart
	[
		'pattern' => [
			'(account)/avatar',
			'users/(:any)/avatar',
		],
		'method'  => 'POST',
		'action'  => function (string $id) {
			return $this->upload(
				single: true,
				callback: function ($source, $filename) use ($id) {
					$user   = Find::user($id);
					$method = $user->avatar() === null ? 'createAvatar' : 'replaceAvatar';

					return $user->$method(
						source: $source,
						extension: F::extension($filename),
						move: true
					)->avatar();
				},
				preflight: function () use ($id) {
					$user = Find::user($id);

					if ($user->guards()->isAvailable('update') !== true) {
						throw new PermissionException(
							key: 'user.update.permission',
							data: ['name' => $user->username()]
						);
					}
				},
			);
		}
	],
	// @codeCoverageIgnoreEnd
	[
		'pattern' => [
			'(account)/avatar',
			'users/(:any)/avatar',
		],
		'method'  => 'DELETE',
		'action'  => function (string $id) {
			return Find::user($id)->deleteAvatar();
		}
	],
	[
		'pattern' => [
			'(account)/blueprint',
			'users/(:any)/blueprint',
		],
		'method'  => 'GET',
		'action'  => function (string $id) {
			return Find::user($id)->blueprint();
		}
	],
	[
		'pattern' => [
			'(account)/blueprints',
			'users/(:any)/blueprints',
		],
		'method'  => 'GET',
		'action'  => function (string $id) {
			return Find::user($id)->blueprints($this->request()->query()->string('field'));
		}
	],
	[
		'pattern' => [
			'(account)/email',
			'users/(:any)/email',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return Find::user($id)->changeEmail($this->request()->body()->string('email', ''));
		}
	],
	[
		'pattern' => [
			'(account)/language',
			'users/(:any)/language',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return Find::user($id)->changeLanguage($this->request()->body()->string('language', ''));
		}
	],
	[
		'pattern' => [
			'(account)/name',
			'users/(:any)/name',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return Find::user($id)->changeName($this->request()->body()->string('name', ''));
		}
	],
	[
		'pattern' => [
			'(account)/password',
			'users/(:any)/password',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			$user        = Find::user($id);
			$currentUser = $this->kirby()->user();

			// validate password of acting user unless they have logged in to reset it;
			// always validate password of acting user when changing password of other users
			if (
				$this->kirby()->auth()->isResettingPassword($this->session()) !== true ||
				$user->is($currentUser) !== true
			) {
				$this->kirby()->auth()->ensurePassword(
					$currentUser,
					$this->request()->body()->string('currentPassword')
				);
			}

			$result = $user->changePassword($this->request()->body()->string('password', ''));

			// if we changed the password of the current user…
			if ($user->isLoggedIn() === true) {
				// …don't allow additional resets (now the password is known again)
				$this->session()->remove('kirby.resetPassword');
			}

			return $result;
		}
	],
	[
		'pattern' => [
			'(account)/role',
			'users/(:any)/role',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return Find::user($id)->changeRole($this->request()->body()->string('role', ''));
		}
	],
	[
		'pattern' => [
			'(account)/roles',
			'users/(:any)/roles',
		],
		'action'  => function (string $id) {
			return Find::user($id)->roles();
		}
	],
	[
		'pattern' => [
			'(account)/fields/(:any)/(:all?)',
			'users/(:any)/fields/(:any)/(:all?)',
		],
		'method'  => 'ALL',
		'action'  => function (string $id, string $fieldName, string|null $path = null) {
			$field = Form::for(Find::user($id))->field($fieldName);
			return $this->forward($field, $path);
		}
	],
	// @codeCoverageIgnoreEnd
];
