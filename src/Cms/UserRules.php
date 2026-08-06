<?php

namespace Kirby\Cms;

use SensitiveParameter;

/**
 * Validators for all user actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @deprecated 6.0.0 Use `$user->guards()` instead
 */
class UserRules
{
	public static function changeEmail(User $user, string $email): void
	{
		$user->guards()->ensureExecutable('changeEmail', $email);
	}

	public static function changeLanguage(User $user, string $language): void
	{
		$user->guards()->ensureExecutable('changeLanguage', $language);
	}

	public static function changeName(User $user, string $name): void
	{
		$user->guards()->ensureExecutable('changeName', $name);
	}

	public static function changePassword(
		User $user,
		#[SensitiveParameter]
		string $password
	): void {
		$user->guards()->ensureExecutable('changePassword', $password);
	}

	public static function changeRole(User $user, string $role): void
	{
		$user->guards()->ensureExecutable('changeRole', $role);
	}

	public static function changeSecret(
		User $user,
		string $secret,
		#[SensitiveParameter]
		mixed $content
	): void {
		$user->guards()->ensureExecutable('changeSecret', $secret, $content);
	}

	public static function changeTotp(
		User $user,
		#[SensitiveParameter]
		string|null $secret
	): void {
		$user->guards()->ensureExecutable('changeSecret', 'totp', $secret);
	}

	public static function create(User $user, array $props = []): void
	{
		$user->guards()->ensureExecutable('create', $props);
	}

	public static function createAvatar(User $user, string $source, string $extension): void
	{
		$user->guards()->ensureExecutable('createAvatar', $source, $extension);
	}

	public static function delete(User $user): void
	{
		$user->guards()->ensureExecutable('delete');
	}

	public static function deleteAvatar(User $user): void
	{
		$user->guards()->ensureExecutable('deleteAvatar');
	}

	public static function replaceAvatar(User $user, string $source, string $extension): void
	{
		$user->guards()->ensureExecutable('replaceAvatar', $source, $extension);
	}

	public static function update(
		User $user,
		array $values = [],
		array $strings = []
	): void {
		$user->guards()->ensureExecutable('update', $values, $strings);
	}

	public static function validEmail(
		User $user,
		string $email,
		bool $strict = false
	): void {
		$user->guards()->validators()->validateEmail($email, $strict);
	}

	public static function validAvatar(User $user, string $source, string $extension): void
	{
		$user->guards()->validators()->validateAvatar($source, $extension);
	}

	public static function validId(User $user, string $id): void
	{
		$user->guards()->validators()->validateId($id);
	}

	public static function validLanguage(User $user, string $language): void
	{
		$user->guards()->validators()->validateLanguage($language);
	}

	public static function validPassword(
		User $user,
		#[SensitiveParameter]
		string $password
	): void {
		$user->guards()->validators()->validateNewPassword($password);
	}

	public static function validRole(User $user, string $role): void
	{
		$user->guards()->validators()->validateRole($role);
	}
}
