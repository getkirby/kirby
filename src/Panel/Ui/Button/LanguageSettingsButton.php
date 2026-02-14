<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\App;
use Kirby\Cms\Language;

/**
 * View button to update settings of a language
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 *
 * @unstable
 */
class LanguageSettingsButton extends ViewButton
{
	public function __construct(Language $language)
	{
		$user       = App::instance()->user();
		$permission = $user?->role()->permissions()->for('languages', 'update');

		parent::__construct(
			dialog: 'languages/' . $language->id() . '/update',
			disabled: $permission !== true,
			icon: 'cog',
			title: $this->i18n('settings'),
		);
	}
}
