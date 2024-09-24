<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\Totp;
use Kirby\Toolkit\V;
use SensitiveParameter;

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
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the address
	 */
	public static function changeEmail(User $user, string $email): void
	{
		if ($user->permissions()->changeEmail() !== true) {
			throw new PermissionException(
				key: 'user.changeEmail.permission',
				data: ['name' => $user->username()]
			);
		}

		static::validEmail($user, $email);
	}

	/**
	 * Validates if the language can be changed
	 *
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the language
	 */
	public static function changeLanguage(User $user, string $language): void
	{
		if ($user->permissions()->changeLanguage() !== true) {
			throw new PermissionException(
				key: 'user.changeLanguage.permission',
				data: ['name' => $user->username()]
			);
		}

		static::validLanguage($user, $language);
	}

	/**
	 * Validates if the name can be changed
	 *
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the name
	 */
	public static function changeName(User $user, string $name): void
	{
		if ($user->permissions()->changeName() !== true) {
			throw new PermissionException(
				key: 'user.changeName.permission',
				data: ['name' => $user->username()]
			);
		}
	}

	/**
	 * Validates if the password can be changed
	 *
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the password
	 */
	public static function changePassword(
		User $user,
		#[SensitiveParameter]
		string $password
	): void {
		if ($user->permissions()->changePassword() !== true) {
			throw new PermissionException(
				key: 'user.changePassword.permission',
				data: ['name' => $user->username()]
			);
		}

		static::validPassword($user, $password);
	}

	/**
	 * Validates if the role can be changed
	 *
	 * @throws \Kirby\Exception\LogicException If the user is the last admin
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the role
	 */
	public static function changeRole(User $user, string $role): void
	{
		// prevent non-admins making a user to admin
		if (
			$user->kirby()->user()->isAdmin() === false &&
			$role === 'admin'
		) {
			throw new PermissionException(
				key: 'user.changeRole.toAdmin'
			);
		}

		// prevent demoting the last admin
		if ($role !== 'admin' && $user->isLastAdmin() === true) {
			throw new LogicException(
				key: 'user.changeRole.lastAdmin',
				data: ['name' => $user->username()]
			);
		}

		// check permissions
		if ($user->permissions()->changeRole() !== true) {
			throw new PermissionException(
				key: 'user.changeRole.permission',
				data: ['name' => $user->username()]
			);
		}

		// prevent changing to role that is not available for user
		if ($user->roles('change')->find($role) instanceof Role === false) {
			throw new InvalidArgumentException(
				key: 'user.role.invalid',
			);
		}
	}

	/**
	 * Validates if the TOTP can be changed
	 * @since 4.0.0
	 *
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the password
	 */
	public static function changeTotp(
		User $user,
		#[SensitiveParameter]
		string|null $secret
	): void {
		$currentUser = $user->kirby()->user();

		if (
			$currentUser->is($user) === false &&
			$currentUser->isAdmin() === false
		) {
			throw new PermissionException(
				message: 'You cannot change the time-based code for ' . $user->email()
			);
		}

		// safety check to avoid accidental insecure secrets;
		// throws an exception for secrets of the wrong length
		if ($secret !== null) {
			new Totp($secret);
		}
	}

	/**
	 * Validates if the user can be created
	 *
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to create a new user
	 */
	public static function create(User $user, array $props = []): void
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
			return;
		}

		// check user permissions (if not on install)
		if (
			$user->kirby()->users()->count() > 0 &&
			$user->permissions()->can('create') !== true
		) {
			throw new PermissionException(
				key: 'user.create.permission'
			);
		}
	}

	/**
	 * Validates if the user can be deleted
	 *
	 * @throws \Kirby\Exception\LogicException If this is the last user or last admin, which cannot be deleted
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to delete this user
	 */
	public static function delete(User $user): void
	{
		if ($user->isLastAdmin() === true) {
			throw new LogicException(
				key: 'user.delete.lastAdmin'
			);
		}

		if ($user->isLastUser() === true) {
			throw new LogicException(
				key: 'user.delete.lastUser'
			);
		}

		if ($user->permissions()->delete() !== true) {
			throw new PermissionException(
				key: 'user.delete.permission',
				data: ['name' => $user->username()]
			);
		}
	}

	/**
	 * Validates if the user can be updated
	 *
	 * @throws \Kirby\Exception\PermissionException If the user it not allowed to update this user
	 */
	public static function update(
		User $user,
		array $values = [],
		array $strings = []
	): void {
		if ($user->permissions()->update() !== true) {
			throw new PermissionException(
				key: 'user.update.permission',
				data: ['name' => $user->username()]
			);
		}
	}

	/**
	 * Validates an email address
	 *
	 * @throws \Kirby\Exception\DuplicateException If the email address already exists
	 * @throws \Kirby\Exception\InvalidArgumentException If the email address is invalid
	 */
	public static function validEmail(
		User $user,
		string $email,
		bool $strict = false
	): void {
		if (V::email($email ?? null) === false) {
			throw new InvalidArgumentException(
				key: 'user.email.invalid'
			);
		}

		$duplicate = match ($strict) {
			true  => $user->kirby()->users()->find($email),
			false => $user->kirby()->users()->not($user)->find($email)
		};

		if ($duplicate) {
			throw new DuplicateException(
				key: 'user.duplicate',
				data: ['email' => $email]
			);
		}
	}

	/**
	 * Validates a user id
	 *
	 * @throws \Kirby\Exception\DuplicateException If the user already exists
	 */
	public static function validId(User $user, string $id): void
	{
		if (in_array($id, ['account', 'kirby', 'nobody'], true) === true) {
			throw new InvalidArgumentException(
				message: '"' . $id . '" is a reserved word and cannot be used as user id'
			);
		}

		if ($user->kirby()->users()->find($id)) {
			throw new DuplicateException(
				message: 'A user with this id exists'
			);
		}
	}

	/**
	 * Validates a user language code
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language does not exist
	 */
	public static function validLanguage(User $user, string $language): void
	{
		if (in_array($language, $user->kirby()->translations()->keys(), true) === false) {
			throw new InvalidArgumentException(key: 'user.language.invalid');
		}
	}

	/**
	 * Validates a password
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is too short
	 */
	public static function validPassword(
		User $user,
		#[SensitiveParameter]
		string $password
	): void {
		// too short passwords are ineffective
		if (Str::length($password ?? null) < 8) {
			throw new InvalidArgumentException(key: 'user.password.invalid');
		}

		// too long passwords can cause DoS attacks
		// and are therefore blocked in the auth system
		// (blocked here as well to avoid passwords
		// that cannot be used to log in)
		if (Str::length($password ?? null) > 1000) {
			throw new InvalidArgumentException(key: 'user.password.excessive');
		}
	}

	/**
	 * Validates a user role
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the user role does not exist
	 * @deprecated 4.5.0
	 */
	public static function validRole(User $user, string $role): void
	{
		if ($user->kirby()->roles()->find($role) instanceof Role === false) {
			throw new InvalidArgumentException(
				key: 'user.role.invalid',
			);
		}
	}
}
