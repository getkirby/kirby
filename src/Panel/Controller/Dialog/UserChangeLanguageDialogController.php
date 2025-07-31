<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use Kirby\Toolkit\I18n;

/**
 * Controls the Panel dialog for changing the language of a user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserChangeLanguageDialogController extends UserDialogController
{
	public function load(): Dialog
	{
		return new FormDialog(
			fields: [
				'translation' => Field::translation(['required' => true])
			],
			submitButton: I18n::translate('change'),
			value: [
				'translation' => $this->user->language()
			]
		);
	}

	public function submit(): array
	{
		$this->user = $this->user->changeLanguage(
			language: $this->request->get('translation')
		);

		return [
			'event'  => 'user.changeLanguage',
			'reload' => [
				'globals' => 'translation'
			]
		];
	}
}
