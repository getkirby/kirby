<?php

use Kirby\Exception\Exception;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;

/**
 * User Routes
 */
return [
	[
		'pattern' => 'users',
		'method'  => 'GET',
		'action'  => function () {
			return $this->users()->sort('username', 'asc', 'email', 'asc');
		}
	],
	[
		'pattern' => 'users',
		'method'  => 'POST',
		'action'  => function () {
			return $this->users()->create($this->requestBody());
		}
	],
	[
		'pattern' => 'users/search',
		'method'  => 'GET|POST',
		'action'  => function () {
			if ($this->requestMethod() === 'GET') {
				return $this->users()->search($this->requestQuery('q'));
			}

			return $this->users()->query($this->requestBody());
		}
	],
	[
		'pattern' => [
			'(account)',
			'users/(:any)',
		],
		'method'  => 'GET',
		'action'  => function (string $id) {
			return $this->user($id);
		}
	],
	[
		'pattern' => [
			'(account)',
			'users/(:any)',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->user($id)->update($this->requestBody(), $this->language(), true);
		}
	],
	[
		'pattern' => [
			'(account)',
			'users/(:any)',
		],
		'method'  => 'DELETE',
		'action'  => function (string $id) {
			return $this->user($id)->delete();
		}
	],
	[
		'pattern' => [
			'(account)/avatar',
			'users/(:any)/avatar',
		],
		'method'  => 'GET',
		'action'  => function (string $id) {
			return $this->user($id)->avatar();
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
					$type = F::type($filename);
					if ($type !== 'image') {
						throw new Exception([
							'key'  => 'file.type.invalid',
							'data' => compact('type')
						]);
					}

					$mime = F::mime($source);
					if (Str::startsWith($mime, 'image/') !== true) {
						throw new Exception([
							'key'  => 'file.mime.invalid',
							'data' => compact('mime')
						]);
					}

					// delete the old avatar
					$this->user($id)->avatar()?->delete();

					$props = [
						'filename' => 'profile.' . F::extension($filename),
						'template' => 'avatar',
						'source'   => $source
					];

					// move the source file from the temp dir
					return $this->user($id)->createFile($props, true);
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
			return $this->user($id)->avatar()->delete();
		}
	],
	[
		'pattern' => [
			'(account)/blueprint',
			'users/(:any)/blueprint',
		],
		'method'  => 'GET',
		'action'  => function (string $id) {
			return $this->user($id)->blueprint();
		}
	],
	[
		'pattern' => [
			'(account)/blueprints',
			'users/(:any)/blueprints',
		],
		'method'  => 'GET',
		'action'  => function (string $id) {
			return $this->user($id)->blueprints($this->requestQuery('section'));
		}
	],
	[
		'pattern' => [
			'(account)/email',
			'users/(:any)/email',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->user($id)->changeEmail($this->requestBody('email'));
		}
	],
	[
		'pattern' => [
			'(account)/language',
			'users/(:any)/language',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->user($id)->changeLanguage($this->requestBody('language'));
		}
	],
	[
		'pattern' => [
			'(account)/name',
			'users/(:any)/name',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->user($id)->changeName($this->requestBody('name'));
		}
	],
	[
		'pattern' => [
			'(account)/password',
			'users/(:any)/password',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->user($id)->changePassword($this->requestBody('password'));
		}
	],
	[
		'pattern' => [
			'(account)/role',
			'users/(:any)/role',
		],
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->user($id)->changeRole($this->requestBody('role'));
		}
	],
	[
		'pattern' => [
			'(account)/roles',
			'users/(:any)/roles',
		],
		'action'  => function (string $id) {
			return $this->user($id)->roles();
		}
	],
	[
		'pattern' => [
			'(account)/sections/(:any)',
			'users/(:any)/sections/(:any)',
		],
		'method'  => 'GET',
		'action'  => function (string $id, string $sectionName) {
			if ($section = $this->user($id)->blueprint()->section($sectionName)) {
				return $section->toResponse();
			}
		}
	],
	[
		'pattern' => [
			'(account)/fields/(:any)/(:all?)',
			'users/(:any)/fields/(:any)/(:all?)',
		],
		'method'  => 'ALL',
		'action'  => function (string $id, string $fieldName, string $path = null) {
			return $this->fieldApi($this->user($id), $fieldName, $path);
		}
	],
];
