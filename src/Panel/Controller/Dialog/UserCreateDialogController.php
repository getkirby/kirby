<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Controls the Panel dialog for changing the email of a user
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class UserCreateDialogController extends DialogController
{
	public function load(): Dialog
	{
		$roles = $this->kirby->roles()->canBeCreated();

		// get default value for role
		$role = $this->request->string('role');
		$role = $role !== null ? $roles->find($role)?->id() : null;

		// fall back to the first role that can be created,
		// also if the role field is hidden for a single role
		$role ??= $roles->first()?->id();

		// get role field definition, incl. available role options
		$roles = Field::role(
			roles: $roles,
			props: ['required' => true]
		);

		$policy = $this->kirby->auth()->passwords();

		return new FormDialog(
			fields: [
				'name'  => Field::username(),
				'email' => Field::email([
					'link'     => false,
					'required' => true
				]),
				'password'     => Field::password([
					'autocomplete' => 'new-password',
					'help'         => $policy->hint(),
					'minlength'    => $policy->minlength()
				]),
				'translation'  => Field::translation([
					'required' => true
				]),
				'role' => $roles
			],
			submitButton: $this->i18n('create'),
			value: [
				'name'        => '',
				'email'       => '',
				'password'    => '',
				'translation' => $this->kirby->panelLanguage(),
				'role'        => $role
			]
		);
	}

	public function submit(): array
	{
		$this->kirby->users()->create([
			'name'     => $this->request->string('name'),
			'email'    => $this->request->string('email'),
			'password' => $this->request->string('password'),
			'language' => $this->request->string('translation'),
			'role'     => $this->request->string('role')
		]);

		return [
			'event' => 'user.create'
		];
	}
}
