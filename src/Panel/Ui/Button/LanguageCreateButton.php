<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\App;

/**
 * View button to create a new language
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 *
 * @unstable
 */
class LanguageCreateButton extends ViewButton
{
	public function __construct()
	{
		$user       = App::instance()->user();
		$permission = $user?->role()->permissions()->for('languages', 'create');

		parent::__construct(
			dialog: 'languages/create',
			disabled: $permission !== true,
			icon: 'add',
			text: $this->i18n('language.create'),
		);
	}
}
