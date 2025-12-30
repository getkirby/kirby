<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;

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
	public function load(): View
	{
		$methods = $this->methods();
		$method  = $this->request->get('method');

		if ($method === null || in_array($method, $methods, true) === false) {
			$method = $methods[0];
		}

		return new View(
			component: 'k-login-view',
			method:    $method,
			methods:   $methods,
			pending:   $this->pending(),
			value:     $this->value()
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

	public function value(): array
	{
		return [];
	}
}
