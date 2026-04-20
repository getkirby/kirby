<?php

use Kirby\Cms\Find;
use Kirby\Filesystem\F;

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
			return Find::users()->create($this->requestBody());
		}
	],
	[
		'pattern' => 'users/search',
		'method'  => 'GET|POST',
		'action'  => function () {
			if ($this->requestMethod() === 'GET') {
				return Find::users()->search($this->requestQuery('q'));
			}

			return Find::users()->query($this->requestBody());
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
			return Find::user($id)->update($this->requestBody(), $this->language(), true);
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
				function ($source, $filename) use ($id) {
					$user   = Find::user($id);
					$method = $user->avatar() === null ? 'createAvatar' : 'replaceAvatar';

					return $user->$method(
						source: $source,
						extension: F::extension($filename),
						move: true
					)->avatar();
				},
				single: true
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
			return Find::user($id)->blueprints($this->requestQuery('section'));
		}
	],
	[
		'pattern' => [
			'(account)/email',
			'users/(:any)/email',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return Find::user($id)->changeEmail($this->requestBody('email'));
		}
	],
	[
		'pattern' => [
			'(account)/language',
			'users/(:any)/language',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return Find::user($id)->changeLanguage($this->requestBody('language'));
		}
	],
	[
		'pattern' => [
			'(account)/name',
			'users/(:any)/name',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return Find::user($id)->changeName($this->requestBody('name'));
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
			if ($this->session()->get('kirby.resetPassword') !== true || $user->is($currentUser) !== true) {
				$currentUser->validatePassword($this->requestBody('currentPassword'));
			}

			$result = $user->changePassword($this->requestBody('password'));

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
			return Find::user($id)->changeRole($this->requestBody('role'));
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
			return $this->fieldApi(Find::user($id), $fieldName, $path);
		}
	],
	[
		'pattern' => [
			'(account)/sections/(:any)',
			'users/(:any)/sections/(:any)',
		],
		'method'  => 'GET',
		'action'  => function (string $id, string $sectionName) {
			if ($section = Find::user($id)->blueprint()->section($sectionName)) {
				return $section->toResponse();
			}
		}
	],
	[
		'pattern' => [
			'(account)/sections/(:any)/(:all?)',
			'users/(:any)/sections/(:any)/(:all?)',
		],
		'method'  => 'ALL',
		'action'  => function (string $id, string $sectionName, string|null $path = null) {
			return $this->sectionApi(Find::user($id), $sectionName, $path);
		}
	],
	// @codeCoverageIgnoreEnd
];
