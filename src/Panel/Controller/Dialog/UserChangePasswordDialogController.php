<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\UserRules;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Controls the Panel dialog for changing the password of a user
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class UserChangePasswordDialogController extends UserDialogController
{
	/**
	 * Whether the current password confirmation can be skipped
	 * (only if user is changing their own password and has no password yet)
	 */
	protected function canSkipConfirmation(): bool
	{
		return
			$this->user->isLoggedIn() === true &&
			$this->user->hasPassword() === false;
	}

	protected function fields(): array
	{
		$fields = [
			'currentPassword' => Field::password([
				'label'        => $this->i18n('user.changePassword.' . ($this->isCurrentUser() ? 'current' : 'own')),
				'autocomplete' => 'current-password',
				'help'         => $this->i18n('account') . ': ' . $this->kirby->user()->email(),
			]),
			'line' => [
				'type' => 'line',
			],
			'password' => Field::password([
				'label'        => $this->i18n('user.changePassword.new'),
				'autocomplete' => 'new-password',
				'help'         => $this->i18n('account') . ': ' . $this->user->email(),
			]),
			'passwordConfirmation' => Field::password([
				'label'        => $this->i18n('user.changePassword.new.confirm'),
				'autocomplete' => 'new-password'
			])
		];

		// if the currently logged in user tries to change their own password
		// and has no password so far, password confirmation can be skipped
		if ($this->canSkipConfirmation() === true) {
			unset($fields['currentPassword'], $fields['line']);
		}

		return $fields;
	}

	protected function isCurrentUser(): bool
	{
		return $this->kirby->user()->is($this->user);
	}

	public function load(): Dialog
	{
		return new FormDialog(
			fields: $this->fields(),
			submitButton: $this->i18n('change')
		);
	}

	public function submit(): array
	{
		$currentPassword      = $this->request->get('currentPassword');
		$password             = $this->request->get('password');
		$passwordConfirmation = $this->request->get('passwordConfirmation');

		// validate the current password of the acting user
		try {
			if ($this->canSkipConfirmation() === false) {
				$this->kirby->user()->validatePassword($currentPassword);
			}
		} catch (Exception) {
			// catching and re-throwing exception to avoid automatic
			// sign-out of current user from the Panel
			throw new InvalidArgumentException(key: 'user.password.wrong');
		}

		// validate the new password
		UserRules::validPassword($this->user, $password ?? '');

		// compare passwords
		if ($password !== $passwordConfirmation) {
			throw new InvalidArgumentException(key: 'user.password.notSame');
		}

		// change password if everything's fine
		$this->user = $this->user->changePassword($password);

		return [
			'event' => 'user.changePassword'
		];
	}
}
