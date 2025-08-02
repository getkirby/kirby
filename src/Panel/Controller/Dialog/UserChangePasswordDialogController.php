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
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserChangePasswordDialogController extends UserDialogController
{
	public function load(): Dialog
	{
		return new FormDialog(
			fields: [
				'currentPassword' => Field::password([
					'label'        => $this->i18n('user.changePassword.current'),
					'autocomplete' => 'current-password'
				]),
				'password' => Field::password([
					'label'        => $this->i18n('user.changePassword.new'),
					'autocomplete' => 'new-password'
				]),
				'passwordConfirmation' => Field::password([
					'label'        => $this->i18n('user.changePassword.new.confirm'),
					'autocomplete' => 'new-password'
				])
			],
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
			$this->kirby->user()->validatePassword($currentPassword);
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
