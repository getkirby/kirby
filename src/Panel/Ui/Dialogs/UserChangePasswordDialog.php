<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\User;
use Kirby\Cms\UserRules;
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
				'password' => Field::password([
					'label' => I18n::translate('user.changePassword.new'),
				]),
				'passwordConfirmation' => Field::password([
					'label' => I18n::translate('user.changePassword.new.confirm'),
				])
			],
			submitButton: I18n::translate('change'),
		);
	}

	public function submit(): array
	{
		$password             = $this->request->get('password');
		$passwordConfirmation = $this->request->get('passwordConfirmation');

		// validate the password
		UserRules::validPassword($this->user, $password ?? '');

		// compare passwords
		if ($password !== $passwordConfirmation) {
			throw new InvalidArgumentException(
				key: 'user.password.notSame'
			);
		}

		// change password if everything's fine
		$this->user->changePassword($password);

		return [
			'event' => 'user.changePassword'
		];
	}
}
