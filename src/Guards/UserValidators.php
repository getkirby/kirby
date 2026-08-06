<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\Role;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use SensitiveParameter;

/**
 * Validators for the input of `$user` actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserValidators extends ModelValidators
{
	/**
	 * @var User
	 */
	protected Model $model;

	/**
	 * Validates the input of the `changeEmail` action
	 */
	protected function ensureToChangeEmail(string $email): void
	{
		$this->validateEmail($email);
	}

	/**
	 * Validates the input of the `changeLanguage` action
	 */
	protected function ensureToChangeLanguage(string $language): void
	{
		$this->validateLanguage($language);
	}

	/**
	 * Validates the input of the `changePassword` action
	 */
	protected function ensureToChangePassword(
		#[SensitiveParameter]
		string $password
	): void {
		$this->validatePassword($password);
	}

	/**
	 * Validates the input of the `changeRole` action
	 */
	protected function ensureToChangeRole(string $role): void
	{
		$this->validateAssignableRole($role);
	}

	/**
	 * Validates the user that is about to be created
	 */
	protected function ensureToCreate(array $props = []): void
	{
		$this->validateId($this->model->id());
		$this->validateEmail($this->model->email(), strict: true);
		$this->validateLanguage($this->model->language());
		$this->validatePassword($props['password'] ?? '', allowEmpty: true);
		$this->validateCreatableRole($props['role'] ?? 'default');
	}

	/**
	 * Validates the input of the `createAvatar` action
	 */
	protected function ensureToCreateAvatar(string $source, string $extension): void
	{
		$this->validateAvatar($source, $extension);
	}

	/**
	 * Validates the very first user of an installation, which
	 * has no role to check against, but needs a password
	 */
	protected function ensureToCreateFirstUser(array $props = []): void
	{
		$this->validateId($this->model->id());
		$this->validateEmail($this->model->email(), strict: true);
		$this->validateLanguage($this->model->language());
		$this->validatePassword($props['password'] ?? '');
	}

	/**
	 * Validates the input of the `replaceAvatar` action
	 */
	protected function ensureToReplaceAvatar(string $source, string $extension): void
	{
		$this->validateAvatar($source, $extension);
	}

	/**
	 * Validates that the role can be assigned to the user
	 * by the currently authenticated user
	 */
	public function validateAssignableRole(string $role): void
	{
		if ($this->model->roles()->find($role) instanceof Role === false) {
			$this->error(
				key: 'user.role.invalid'
			);
		}
	}

	/**
	 * Validates that the given file can be used as avatar
	 */
	public function validateAvatar(string $source, string $extension): void
	{
		$this->validateAvatarType($extension);
		$this->validateAvatarMime($source);
	}

	/**
	 * Validates that the content of the avatar file is an image
	 */
	public function validateAvatarMime(string $source): void
	{
		$mime = F::mime($source);

		if (Str::startsWith($mime, 'image/') !== true) {
			$this->error(
				key: 'file.mime.invalid',
				data: compact('mime')
			);
		}
	}

	/**
	 * Validates that the extension of the avatar file
	 * belongs to an image type
	 */
	public function validateAvatarType(string $extension): void
	{
		$type = F::extensionToType($extension);

		if ($type !== 'image') {
			$this->error(
				key: 'file.type.invalid',
				data: compact('type')
			);
		}
	}

	/**
	 * Validates that the role can be given to a new user
	 * by the currently authenticated user. The very first
	 * user of an installation must always be an admin.
	 */
	public function validateCreatableRole(string $role): void
	{
		if ($this->model->kirby()->users()->count() === 0) {
			// the first user has to be an admin
			if ($role !== 'admin') {
				$this->error(
					key: 'user.role.invalid'
				);
			}

			return;
		}

		if (
			in_array($role, ['', 'default', 'nobody'], true) === false &&
			$this->model->kirby()->roles()->canBeCreated()->find($role) instanceof Role === false
		) {
			$this->error(
				key: 'user.role.invalid'
			);
		}
	}

	/**
	 * Validates that the email address is formatted correctly
	 * and is not used by another user yet
	 *
	 * @param bool $strict If `true`, the user itself is not ignored,
	 *                     which is needed for users that don't exist yet
	 */
	public function validateEmail(string $email, bool $strict = false): void
	{
		if (V::email($email) === false) {
			$this->error(
				key: 'user.email.invalid'
			);
		}

		$duplicate = match ($strict) {
			true  => $this->model->kirby()->users()->find($email),
			false => $this->model->kirby()->users()->not($this->model)->find($email)
		};

		if ($duplicate !== null) {
			throw new DuplicateException(
				key: 'user.duplicate',
				data: ['email' => $email]
			);
		}
	}

	/**
	 * Validates that the user id is not reserved for
	 * internal purposes and not taken by another user
	 */
	public function validateId(string $id): void
	{
		if (in_array($id, ['account', 'kirby', 'nobody'], true) === true) {
			$this->error(
				key: 'user.id.reserved',
				data: compact('id')
			);
		}

		if ($this->model->kirby()->users()->find($id) !== null) {
			throw new DuplicateException(
				message: 'A user with this id exists'
			);
		}
	}

	/**
	 * Validates that a Panel translation exists
	 * for the given language code
	 */
	public function validateLanguage(string $language): void
	{
		if (in_array($language, $this->model->kirby()->translations()->keys(), true) === false) {
			$this->error(
				key: 'user.language.invalid'
			);
		}
	}

	/**
	 * Validates the password against the password policy
	 * configured in the `auth.passwords` option
	 *
	 * @param bool $allowEmpty If `true`, an empty password is accepted,
	 *                         which is needed for users that are created
	 *                         without a password
	 */
	public function validatePassword(
		#[SensitiveParameter]
		string $password,
		bool $allowEmpty = false
	): void {
		if ($allowEmpty === true && $password === '') {
			return;
		}

		// validate against the configured policy (`auth.passwords`)
		$this->model->kirby()->auth()->passwords()->validate($password);
	}

	/**
	 * Validates that the role exists at all. This does not
	 * check if the currently authenticated user is allowed to
	 * give it to someone. Use `::validateAssignableRole()` for that.
	 */
	public function validateRole(string $role): void
	{
		if ($this->model->kirby()->roles()->find($role) instanceof Role === false) {
			$this->error(
				key: 'user.role.invalid'
			);
		}
	}
}
