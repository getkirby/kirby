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
class UserChangeLanguageDialog extends FormDialog
{
	use IsForUser;

	public function __construct(
		public User $user
	) {
		parent::__construct(
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
		$language   = $this->request->get('translation');
		$this->user = $this->user->changeLanguage($language);

		return [
			'event'  => 'user.changeLanguage',
			'reload' => [
				'globals' => '$translation'
			]
		];
	}
}
