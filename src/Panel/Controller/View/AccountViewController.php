<?php

namespace Kirby\Panel\Controller\View;

/**
 * Controls the account view
 *
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
