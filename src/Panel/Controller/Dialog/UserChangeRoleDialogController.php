<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use Kirby\Toolkit\I18n;

/**
 * Controls the Panel dialog for changing the role of a user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
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
						'label'    => I18n::translate('user.changeRole.select'),
						'required' => true,
					]
				)
			],
			submitButton: I18n::translate('user.changeRole'),
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
