<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Controls the Panel dialog for changing the role of a user
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class UserChangeRoleDialogController extends UserDialogController
{
	public function load(): Dialog
	{
		return new FormDialog(
			fields: [
				'role' => Field::role(
					roles: $this->user->roles(),
					props: [
						'label'    => $this->i18n('user.changeRole.select'),
						'required' => true,
					]
				)
			],
			submitButton: $this->i18n('user.changeRole'),
			value: [
				'role' => $this->user->role()->name()
			]
		);
	}

	public function submit(): array
	{
		$this->user = $this->user->changeRole($this->request->get('role'));

		return [
			'event' => 'user.changeRole',
			'user' => $this->user->toArray()
		];
	}
}
