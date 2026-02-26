<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\ModelWithContent;

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
			disabled: $model->lock()->isLocked(),
			icon: 'cog',
			options: $model->panel()->url(true),
			title: $this->i18n('settings'),
		);
	}
}
