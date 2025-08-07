<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Controls the Panel dialog for changing the email of a user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserChangeEmailDialogController extends UserDialogController
{
	public function load(): Dialog
	{
		return new FormDialog(
			fields: [
				'email' => [
					'label'     => $this->i18n('email'),
					'required'  => true,
					'type'      => 'email',
					'preselect' => true
				]
			],
			submitButton: $this->i18n('change'),
			value: [
				'email' => $this->user->email()
			]
		);
	}

	public function submit(): array
	{
		$this->user = $this->user->changeEmail($this->request->get('email'));

		return [
			'event' => 'user.changeEmail'
		];
	}
}
