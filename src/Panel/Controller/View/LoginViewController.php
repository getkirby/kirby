<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Auth\Challenge;
use Kirby\Auth\Method;
use Kirby\Auth\Pending;
use Kirby\Auth\State;
use Kirby\Auth\Status;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\Component;
use Kirby\Panel\Ui\View;
use Kirby\Toolkit\A;
use Throwable;

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
	protected Method|Challenge $current;
	protected Status $status;

	public function __construct(
		string $type = 'method',
		string|null $name = null
	) {
		parent::__construct();

		$this->status = $this->kirby->auth()->status();

		// If arriving at a method route while a challenge is already pending
		// (e.g. via browser back or manual URL), redirect to the challenge route
		if ($type === 'method' && $this->status->state() === State::Pending) {
			Panel::go('login/challenge/' . $this->status->challenge());
		}

		$this->current = match ($type) {
			'method'    => $this->method($name),
			'challenge' => $this->challenge($name),
			default     => Panel::go('login')
		};
	}

	private function challenge(string|null $name): Challenge
	{
		if ($this->status->state() !== State::Pending) {
			Panel::go('login');
		}

		$challenges = $this->kirby->auth()->challenges();
		$type       = $this->status->challenge();

		if ($name !== null && $type !== $name) {
			try {
				$session    = $this->kirby->session();
				$challenges->switch($session, $name);
				$this->status = $this->kirby->auth()->status();
				$type = $this->status->challenge();
			} catch (Throwable) {
				Panel::go('login');
			}
		}

		$email = $this->status->email();
		$user  = $this->kirby->user($email);
		$mode  = $this->status->mode();

		return $challenges->get($type, $user, $mode);
	}

	protected function form(): array
	{
		$data = $this->status->data() ?? new Pending();
		$form = $this->current instanceof Challenge
			? $this->current->form($data)
			: $this->current->form();

		if ($form instanceof Component) {
			$form = $form->render();
		}

		assert($form !== null);

		if ($value = $this->value()) {
			$form['props']['value'] = $value;
		}

		return $form;
	}

	public function load(): View
	{
		return new View(
			component:  'k-login-view',
			form:       $this->form(),
			challenges: $this->challenges(),
			methods:    $this->methods(),
			state:      $this->status->state()->value,
		);
	}

	private function challenges(): array
	{
		if ($this->status->state() !== State::Pending) {
			return [];
		}

		$email       = $this->status->email();
		$user        = $this->kirby->user($email);
		$mode        = $this->status->mode();
		$challenges  = $this->kirby->auth()->challenges();
		$available   = $challenges->available($user, $mode);
		$currentType = $this->status->challenge();

		return A::map(
			$available,
			fn (string $type) => [
				'type'   => $type,
				'label'  => static::i18n('login.challenge.' . $type . '.label'),
				'icon'   => $challenges->class($type)::icon(),
				'active' => $type === $currentType,
			]
		);
	}

	private function method(string|null $name): Method|null
	{
		$methods = $this->kirby->auth()->methods();

		if ($name !== null && $methods->has($name) === true) {
			return $methods->get($name);
		}

		if ($name !== null) {
			Panel::go('login');
		}

		return $methods->firstEnabled();
	}

	private function methods(): array
	{
		$methods = $this->kirby->auth()->methods();
		$enabled = A::map(
			array_keys($methods->enabled()),
			fn ($type) => $methods->get($type),
		);

		$active = $this->current instanceof Method ? $this->current : $enabled[0];

		return A::map(
			$enabled,
			fn (Method $method) => [
				'type'   => $method::type(),
				'label'  => static::i18n('login.method.' . $method::type() . '.label'),
				'icon'   => $method::icon(),
				'active' => $method::type() === $active::type(),
			]
		);
	}

	protected function value(): array
	{
		return [];
	}
}
