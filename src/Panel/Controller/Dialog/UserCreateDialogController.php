<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use Kirby\Toolkit\I18n;

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
class UserCreateDialogController extends DialogController
{
	public function load(): Dialog
	{
		$roles = $this->kirby->roles()->canBeCreated();

		// get default value for role
		if ($role = $this->request->get('role')) {
			$role = $roles->find($role)?->id();
		}

		// get role field definition, incl. available role options
		$roles = Field::role(
			roles: $roles,
			props: ['required' => true]
		);

		return new FormDialog(
			fields: [
				'name'  => Field::username(),
				'email' => Field::email([
					'link'     => false,
					'required' => true
				]),
				'password'     => Field::password([
					'autocomplete' => 'new-password'
				]),
				'translation'  => Field::translation([
					'required' => true
				]),
				'role' => $roles
			],
			submitButton: I18n::translate('create'),
			value: [
				'name'        => '',
				'email'       => '',
				'password'    => '',
				'translation' => $this->kirby->panelLanguage(),
				'role'        => $role ?: $roles['options'][0]['value'] ?? null
			]
		);
	}

	public function submit(): array
	{
		$this->kirby->users()->create([
			'name'     => $this->request->get('name'),
			'email'    => $this->request->get('email'),
			'password' => $this->request->get('password'),
			'language' => $this->request->get('translation'),
			'role'     => $this->request->get('role')
		]);

		return [
			'event' => 'user.create'
		];
	}
}
