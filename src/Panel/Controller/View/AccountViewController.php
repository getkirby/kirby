<?php

namespace Kirby\Panel\Controller\View;

/**
 * Controls the account view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class AccountViewController extends UserViewController
{
	public function component(): string
	{
		return 'k-account-view';
	}
}
