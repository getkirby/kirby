<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\App;
use Kirby\Cms\Language;

/**
 * View button to delete a language
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 *
 * @unstable
 */
class LanguageDeleteButton extends ViewButton
{
	public function __construct(Language $language)
	{
		$user       = App::instance()->user();
		$permission = $user?->role()->permissions()->for('languages', 'delete');

		parent::__construct(
			dialog: 'languages/' . $language->id() . '/delete',
			disabled: $permission !== true,
			icon: 'trash',
			title: $this->i18n('delete'),
		);
	}
}
