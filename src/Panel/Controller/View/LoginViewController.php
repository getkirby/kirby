<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Auth\Methods;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;
use Kirby\Toolkit\A;

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
	protected function availableMethods(): array
	{
		return A::map(
			array_keys($this->methods()->available()),
			fn ($type) => [
				'text' => $this->i18n('login.method.' . $type . '.label'),
				'icon' => $this->methods()->class($type)::icon(),
				'type' => $type
			]
		);
	}

	public function load(): View
	{
		$method = $this->request->get('method');

		if (
			$method === null ||
			$this->methods()->hasAvailable($method) === false
		) {
			$method = $this->methods()->firstAvailable()?->type();
		}

		return new View(
			component: 'k-login-view',
			method:    $method,
			methods:   $this->availableMethods(),
			pending:   $this->pending(),
			value:     $this->value()
		);
	}

	protected function methods(): Methods
	{
		return $this->kirby->auth()->methods();
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
