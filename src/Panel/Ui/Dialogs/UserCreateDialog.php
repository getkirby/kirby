<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

/**
 * Dialog to create a new user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class UserCreateDialog extends FormDialog
{
	public function __construct()
	{
		$this->kirby   = App::instance();
		$this->request = $this->kirby->request();

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

		parent::__construct(
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
