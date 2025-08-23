<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;
use Override;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LoginViewController extends ViewController
{
	#[Override]
	public function load(): View
	{
		return new View(
			component: 'k-login-view',
			methods:   $this->methods(),
			pending:   $this->pending()
		);
	}

	public function methods(): array
	{
		return array_keys($this->kirby->system()->loginMethods());
	}

	public function pending(): array
	{
		$status = $this->kirby->auth()->status();

		return [
			'email'     => $status->email(),
			'challenge' => $status->challenge()
		];
	}

}
