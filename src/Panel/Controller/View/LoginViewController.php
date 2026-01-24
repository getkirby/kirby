<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Auth\Auth;
use Kirby\Auth\Challenge;
use Kirby\Auth\Challenges;
use Kirby\Auth\Method;
use Kirby\Auth\Methods;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\Login;
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
	protected Auth $auth;

	public function __construct()
	{
		parent::__construct();
		$this->auth = $this->kirby->auth();
	}

	protected function alternativeMethods(): array
	{
		$enabled = $this->methods()->enabled();
		$enabled = A::map(
			array_keys($enabled),
			fn ($type) => [
				'text'    => $this->i18n('login.method.' . $type . '.label'),
				'icon'    => $this->methods()->class($type)::icon(),
				'type'    => $type,
				'current' =>
					$this->current() instanceof Method &&
					$this->current()->type() === $type
			]
		);

		return array_values(A::filter(
			$enabled,
			fn ($alternative) => $alternative['current'] === false
		));

	}

	public function challenges(): Challenges
	{
		return $this->auth->challenges();
	}

	public function current(): Method|Challenge
	{
		if ($challenge = $this->auth->challenges()->pending()) {
			return $challenge;
		}


		if ($method = $this->request->get('current')) {
			if ($this->methods()->has($method)) {
				return $this->methods()->get($method);
			}
		}

		return $this->methods()->firstEnabled();
	}

	public function form(): Login
	{
		return $this->current()->form();
	}

	public function load(): View
	{
		return new View(
			component:    'k-login-view',
			current:      $this->current()->type(),
			alternatives: $this->alternativeMethods(),
			form: 	      $this->form()->render(),
			value:        $this->value()
		);
	}

	protected function methods(): Methods
	{
		return $this->auth->methods();
	}

	public function submit(): true
	{
		$current = $this->current();
		$type    = $this->request->get('current');

		match (true) {
			$current instanceof Method    => $this->submitMethod($type),
			$current instanceof Challenge => $this->submitChallenge($type),
		};

		return true;
	}

	protected function submitChallenge(string $challenge): void
	{
		if ($this->challenges()->has($challenge) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid login method'
			);
		}

		$this->auth->verifyChallenge(
			input: $this->request->get('input')
		);
	}

	protected function submitMethod(string $method): void
	{
		if ($this->methods()->has($method) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid login method'
			);
		}

		$this->auth->authenticate(
			method:   $method,
			email:    $this->request->get('email'),
			password: $this->request->get('password'),
			long:     $this->request->get('long', false)
		);
	}

	public function value(): array
	{
		return [];
	}
}
