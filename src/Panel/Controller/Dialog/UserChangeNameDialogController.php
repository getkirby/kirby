<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use Kirby\Toolkit\I18n;

/**
 * Controls the Panel dialog for changing the name of a user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserChangeNameDialogController extends UserDialogController
{
	public function load(): Dialog
	{
		return new FormDialog(
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
		$this->user = $this->user->changeName($this->request->get('name'));

		return [
			'event' => 'user.changeName'
		];
	}
}
