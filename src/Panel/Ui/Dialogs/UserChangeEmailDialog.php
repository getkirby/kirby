<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\User;
use Kirby\Toolkit\I18n;

/**
 * Dialog to change the email address of a user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class UserChangeEmailDialog extends FormDialog
{
	use IsForUser;

	public function __construct(
		public User $user
	) {
		parent::__construct(
			fields: [
				'email' => [
					'label'     => I18n::translate('email'),
					'required'  => true,
					'type'      => 'email',
					'preselect' => true
				]
			],
			submitButton: I18n::translate('change'),
			value: [
				'email' => $this->user->email()
			]
		);
	}

	public function submit(): array
	{
		$email      = $this->request->get('email');
		$this->user = $this->user->changeEmail($email);

		return [
			'event' => 'user.changeEmail'
		];
	}
}
