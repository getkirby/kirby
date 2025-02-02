<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\User;
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
class UserChangeNameDialog extends FormDialog
{
	use IsForUser;

	public function __construct(
		public User $user
	) {
		parent::__construct(
			fields: [
				'name' => Field::username([
					'preselect' => true
				])
			],
			submitButton: I18n::translate('rename'),
			value: [
				'name' => $this->user->name()->value()
			]
		);
	}

	public function submit(): array
	{
		$name = $this->request->get('name');
		$this->user->changeName($name);

		return [
			'event' => 'user.changeName'
		];
	}
}
