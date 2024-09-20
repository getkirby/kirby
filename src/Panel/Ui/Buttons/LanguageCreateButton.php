<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

/**
 * View button to create a new language
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
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
			text: I18n::translate('language.create'),
		);
	}
}
