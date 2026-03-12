<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;

/**
 * Controls the reset password view
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class ResetPasswordViewController extends ViewController
{
	public function load(): View
	{
		return new View(
			component: 'k-reset-password-view',
			breadcrumb: [
				[
					'label' => $this->i18n('view.resetPassword')
				]
			],
			requirePassword: $this->kirby->session()->get('kirby.resetPassword') !== true
		);
	}
}
