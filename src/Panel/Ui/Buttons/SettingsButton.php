<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\ModelWithContent;
use Kirby\Toolkit\I18n;

/**
 * Settings view button for models
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @unstable
 */
class SettingsButton extends ViewButton
{
	public function __construct(
		ModelWithContent $model
	) {
		parent::__construct(
			component: 'k-settings-view-button',
			class: 'k-settings-view-button',
			icon: 'cog',
			options: $model->panel()->url(true),
			title: I18n::translate('settings'),
		);
	}
}
