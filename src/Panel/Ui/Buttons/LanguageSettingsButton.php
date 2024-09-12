<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Toolkit\I18n;

/**
 * View button to update settings of a language
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
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
			title: I18n::translate('settings'),
		);
	}
}
