<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Auth\Challenge;
use Kirby\Auth\Method;
use Kirby\Auth\State;
use Kirby\Auth\Status;
use Kirby\Cms\Auth;
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
	protected Auth $auth;

	public function __construct()
	{
		parent::__construct();
		$this->auth = $this->kirby->auth();
	}

	public function challenge(): string
	{
		$challenge = $this->status()->challenge();
		return Challenge::handler($challenge);
	}

	public function load(): View
	{
		return new View(
			component: 'k-login-view',
			form:      $this->form(),
			method:    $this->method()->type(),
			methods:   $this->methods(),
			pending:   $this->pending(),
			value:     $this->value()
		);
	}

	public function form(): string
	{
		if ($this->status()->state() === State::Pending) {
			return $this->challenge()::form();
		}

		return $this->method()::form();
	}

	public function method(): Method
	{
		$methods = array_keys($this->auth->methods()->available());
		$method  = $this->request->get("method");

		if (in_array($method, $methods, true) === false) {
			$method = $methods[0];
		}

		return $this->auth->methods()->handler($method);
	}

	public function methods(): array
	{
		$methods = array_keys($this->auth->methods()->available());
		$methods = A::map(
			$methods,
			fn (string $method) => $this->auth->methods()->handler($method)
		);
		$methods = A::map(
			$methods,
			fn(Method $method) => [
				'icon' => $method->icon(),
				'text' => $this->i18n('login.method.' . $method->type() . '.label'),
				'type' => $method->type(),
			]
		);

		return $methods;
	}

	public function pending(): array
	{
		$status = $this->status();

		return [
			'email'     => $status->email(),
			'challenge' => $status->challenge()
		];
	}

	public function status(): Status
	{
		return $this->auth->status();
	}

	public function value(): array
	{
		return [];
	}
}
