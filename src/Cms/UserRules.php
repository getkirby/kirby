<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

/**
 * Validators for all user actions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserRules
{
	/**
	 * Validates if the email address can be changed
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $email
	 * @return bool
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the address
	 */
	public static function changeEmail(User $user, string $email): bool
	{
		if ($user->permissions()->changeEmail() !== true) {
			throw new PermissionException([
				'key'  => 'user.changeEmail.permission',
				'data' => ['name' => $user->username()]
			]);
		}

		return static::validEmail($user, $email);
	}

	/**
	 * Validates if the language can be changed
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $language
	 * @return bool
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the language
	 */
	public static function changeLanguage(User $user, string $language): bool
	{
		if ($user->permissions()->changeLanguage() !== true) {
			throw new PermissionException([
				'key'  => 'user.changeLanguage.permission',
				'data' => ['name' => $user->username()]
			]);
		}

		return static::validLanguage($user, $language);
	}

	/**
	 * Validates if the name can be changed
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $name
	 * @return bool
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the name
	 */
	public static function changeName(User $user, string $name): bool
	{
		if ($user->permissions()->changeName() !== true) {
			throw new PermissionException([
				'key'  => 'user.changeName.permission',
				'data' => ['name' => $user->username()]
			]);
		}

		return true;
	}

	/**
	 * Validates if the password can be changed
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $password
	 * @return bool
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the password
	 */
	public static function changePassword(User $user, string $password): bool
	{
		if ($user->permissions()->changePassword() !== true) {
			throw new PermissionException([
				'key'  => 'user.changePassword.permission',
				'data' => ['name' => $user->username()]
			]);
		}

		return static::validPassword($user, $password);
	}

	/**
	 * Validates if the role can be changed
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $role
	 * @return bool
	 * @throws \Kirby\Exception\LogicException If the user is the last admin
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the role
	 */
	public static function changeRole(User $user, string $role): bool
	{
		// protect admin from role changes by non-admin
		if (
			$user->kirby()->user()->isAdmin() === false &&
			$user->isAdmin() === true
		) {
			throw new PermissionException([
				'key'  => 'user.changeRole.permission',
				'data' => ['name' => $user->username()]
			]);
		}

		// prevent non-admins making a user to admin
		if (
			$user->kirby()->user()->isAdmin() === false &&
			$role === 'admin'
		) {
			throw new PermissionException([
				'key'  => 'user.changeRole.toAdmin'
			]);
		}

		static::validRole($user, $role);

		if ($role !== 'admin' && $user->isLastAdmin() === true) {
			throw new LogicException([
				'key'  => 'user.changeRole.lastAdmin',
				'data' => ['name' => $user->username()]
			]);
		}

		if ($user->permissions()->changeRole() !== true) {
			throw new PermissionException([
				'key'  => 'user.changeRole.permission',
				'data' => ['name' => $user->username()]
			]);
		}

		return true;
	}

	/**
	 * Validates if the user can be created
	 *
	 * @param \Kirby\Cms\User $user
	 * @param array $props
	 * @return bool
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to create a new user
	 */
	public static function create(User $user, array $props = []): bool
	{
		static::validId($user, $user->id());
		static::validEmail($user, $user->email(), true);
		static::validLanguage($user, $user->language());

		// the first user must have a password
		if ($user->kirby()->users()->count() === 0 && empty($props['password'])) {
			// trigger invalid password error
			static::validPassword($user, ' ');
		}

		if (empty($props['password']) === false) {
			static::validPassword($user, $props['password']);
		}

		// get the current user if it exists
		$currentUser = $user->kirby()->user();

		// admins are allowed everything
		if ($currentUser?->isAdmin() === true) {
			return true;
		}

		// only admins are allowed to add admins
		$role = $props['role'] ?? null;

		if ($role === 'admin' && $currentUser?->isAdmin() === false) {
			throw new PermissionException([
				'key' => 'user.create.permission'
			]);
		}

		// check user permissions (if not on install)
		if ($user->kirby()->users()->count() > 0) {
			if ($user->permissions()->create() !== true) {
				throw new PermissionException([
					'key' => 'user.create.permission'
				]);
			}
		}

		return true;
	}

	/**
	 * Validates if the user can be deleted
	 *
	 * @param \Kirby\Cms\User $user
	 * @return bool
	 * @throws \Kirby\Exception\LogicException If this is the last user or last admin, which cannot be deleted
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to delete this user
	 */
	public static function delete(User $user): bool
	{
		if ($user->isLastAdmin() === true) {
			throw new LogicException(['key' => 'user.delete.lastAdmin']);
		}

		if ($user->isLastUser() === true) {
			throw new LogicException([
				'key' => 'user.delete.lastUser'
			]);
		}

		if ($user->permissions()->delete() !== true) {
			throw new PermissionException([
				'key'  => 'user.delete.permission',
				'data' => ['name' => $user->username()]
			]);
		}

		return true;
	}

	/**
	 * Validates if the user can be updated
	 *
	 * @param \Kirby\Cms\User $user
	 * @param array $values
	 * @param array $strings
	 * @return bool
	 * @throws \Kirby\Exception\PermissionException If the user it not allowed to update this user
	 */
	public static function update(User $user, array $values = [], array $strings = []): bool
	{
		if ($user->permissions()->update() !== true) {
			throw new PermissionException([
				'key'  => 'user.update.permission',
				'data' => ['name' => $user->username()]
			]);
		}

		return true;
	}

	/**
	 * Validates an email address
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $email
	 * @param bool $strict
	 * @return bool
	 * @throws \Kirby\Exception\DuplicateException If the email address already exists
	 * @throws \Kirby\Exception\InvalidArgumentException If the email address is invalid
	 */
	public static function validEmail(User $user, string $email, bool $strict = false): bool
	{
		if (V::email($email ?? null) === false) {
			throw new InvalidArgumentException([
				'key' => 'user.email.invalid',
			]);
		}

		if ($strict === true) {
			$duplicate = $user->kirby()->users()->find($email);
		} else {
			$duplicate = $user->kirby()->users()->not($user)->find($email);
		}

		if ($duplicate) {
			throw new DuplicateException([
				'key'  => 'user.duplicate',
				'data' => ['email' => $email]
			]);
		}

		return true;
	}

	/**
	 * Validates a user id
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $id
	 * @return bool
	 * @throws \Kirby\Exception\DuplicateException If the user already exists
	 */
	public static function validId(User $user, string $id): bool
	{
		if ($id === 'account') {
			throw new InvalidArgumentException('"account" is a reserved word and cannot be used as user id');
		}

		if ($user->kirby()->users()->find($id)) {
			throw new DuplicateException('A user with this id exists');
		}

		return true;
	}

	/**
	 * Validates a user language code
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $language
	 * @return bool
	 * @throws \Kirby\Exception\InvalidArgumentException If the language does not exist
	 */
	public static function validLanguage(User $user, string $language): bool
	{
		if (in_array($language, $user->kirby()->translations()->keys(), true) === false) {
			throw new InvalidArgumentException([
				'key' => 'user.language.invalid',
			]);
		}

		return true;
	}

	/**
	 * Validates a password
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $password
	 * @return bool
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is too short
	 */
	public static function validPassword(User $user, string $password): bool
	{
		if (Str::length($password ?? null) < 8) {
			throw new InvalidArgumentException([
				'key' => 'user.password.invalid',
			]);
		}

		return true;
	}

	/**
	 * Validates a user role
	 *
	 * @param \Kirby\Cms\User $user
	 * @param string $role
	 * @return bool
	 * @throws \Kirby\Exception\InvalidArgumentException If the user role does not exist
	 */
	public static function validRole(User $user, string $role): bool
	{
		if ($user->kirby()->roles()->find($role) instanceof Role) {
			return true;
		}

		throw new InvalidArgumentException([
			'key' => 'user.role.invalid',
		]);
	}
}
