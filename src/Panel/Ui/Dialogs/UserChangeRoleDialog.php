<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\User;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

/**
 * Dialog to change the role of a user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class UserChangeRoleDialog extends FormDialog
{
	use IsForUser;

	public function __construct(
		public User $user
	) {
		parent::__construct(
			fields: [
				'role' => Field::role(
					roles: $user->roles(),
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
		$role       = $this->request->get('role');
		$this->user = $this->user->changeRole($role);

		return [
			'event' => 'user.changeRole',
			'user'  => $this->user->toArray()
		];
	}
}
