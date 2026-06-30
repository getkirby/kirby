<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Auth\Challenge;
use Kirby\Auth\Method;
use Kirby\Auth\Pending;
use Kirby\Auth\State;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Exception\LogicException;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\Component;
use Kirby\Panel\Ui\View;
use Kirby\Toolkit\A;

/**
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

		$type = $this->status->challenge();

		// View routes are GET and must not mutate session state.
		// If the URL is out of sync with the active challenge,
		// redirect to the active one. Switching happens via POST
		// through LoginRequestController.
		if ($name !== null && $type !== $name) {
			Panel::go('login/challenge/' . $type);
		}

		$challenges = $this->kirby->auth()->challenges();
		$mode       = $this->status->mode();
		$user       = $this->user();

		return $challenges->get($type, $user, $mode);
	}

	private function challenges(): array
	{
		if ($this->status->state() !== State::Pending) {
			return [];
		}

		$mode        = $this->status->mode();
		$user        = $this->user();
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

	protected function form(): array
	{
		$data = $this->status->data() ?? new Pending();
		$form = $this->current instanceof Challenge
			? $this->current->form($data)
			: $this->current->form();

		if ($form instanceof Component) {
			$form = $form->render();
		}

		if ($form === null) {
			throw new LogicException(
				message: 'The login form could not be rendered'
			);
		}

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

	private function method(string|null $name): Method
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
		if ($this->status->state() === State::Pending) {
			return [];
		}

		$methods = $this->kirby->auth()->methods();
		$enabled = A::map(
			array_keys($methods->enabled()),
			fn ($type) => $methods->get($type),
		);

		$active = $this->current instanceof Method ? $this->current::type() : null;

		return A::map(
			$enabled,
			fn (Method $method) => [
				'type'   => $method::type(),
				'label'  => static::i18n('login.method.' . $method::type() . '.label'),
				'icon'   => $method::icon(),
				'active' => $method::type() === $active,
			]
		);
	}

	protected function value(): array
	{
		return [];
	}

	/**
	 * Returns the user the pending challenge belongs to.
	 *
	 * The challenge email may not belong to an existing user:
	 * the account could have been deleted after the challenge
	 * was created, or it never existed and the pending state
	 * was only created to avoid leaking whether
	 * the user exists (see `Auth::createChallenge()`).
	 *
	 * A virtual user lets the view render the form and
	 * resolve challenge availability identically in that case.
	 */
	protected function user(): User
	{
		$email = $this->status->email();
		return $this->kirby->user($email) ?? new User(['email' => $email]);
	}
}
