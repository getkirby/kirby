<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\User;
use Kirby\Cms\UserRules;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class UserChangePasswordDialog extends FormDialog
{
	use IsForUser;

	public function __construct(
		public User $user
	) {
		parent::__construct(
			fields: [
				'currentPassword' => Field::password([
					'label'      => I18n::translate('user.changePassword.current'),
					'autocomplete' => 'current-password'
				]),
				'password' => Field::password([
					'label'        => I18n::translate('user.changePassword.new'),
					'autocomplete' => 'new-password'
				]),
				'passwordConfirmation' => Field::password([
					'label' => I18n::translate('user.changePassword.new.confirm'),
					'autocomplete' => 'new-password'
				])
			],
			submitButton: I18n::translate('change'),
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
			throw new InvalidArgumentException(
				key: 'user.password.wrong'
			);
		}

		// validate the password
		UserRules::validPassword($this->user, $password ?? '');

		// compare passwords
		if ($password !== $passwordConfirmation) {
			throw new InvalidArgumentException(
				key: 'user.password.notSame'
			);
		}

		// change password if everything's fine
		$this->user = $this->user->changePassword($password);

		return [
			'event' => 'user.changePassword'
		];
	}
}
