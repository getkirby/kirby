<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Panel\Ui\Drawer\TextDrawer;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserSecurityCodeMethodDrawerController extends UserDrawerController
{
	public function load(): TextDrawer
	{
		return new TextDrawer(
			icon: 'hashtag',
			text: $this->i18n('login.method.code.description'),
			title: $this->i18n('login.method.code.label')
		);
	}
}
