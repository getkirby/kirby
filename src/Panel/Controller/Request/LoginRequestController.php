<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Auth\Auth;
use Kirby\Auth\State;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\Controller\RequestController;
use Kirby\Panel\Panel;
use Throwable;

/**
 * Handles login view POST requests:
 * authenticates with a method or verifies a challenge
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LoginRequestController extends RequestController
{
	public function __construct(
		protected string $type = 'method',
		protected string|null $name = null
	) {
		parent::__construct();
	}

	protected function authenticate(Auth $auth): void
	{
		$email  = $this->request->get('email');
		$secret = $this->request->get('password');
		$long   = $this->request->get('long') === true;

		$result = $auth->authenticate($this->name, $email, $secret, $long);

		if ($result instanceof User) {
			Panel::go($this->kirby->panel()->home()->url());
		}

		// Challenge was created → navigate to the challenge route
		// so the URL reflects the new state and the challenge form renders
		Panel::go('login/challenge/' . $result->challenge());
	}

	public function load(): array
	{
		$auth = $this->kirby->auth();

		// CSRF check
		if ($auth->type() === 'session' && $auth->csrf() === false) {
			throw new InvalidArgumentException(
				message: 'Invalid CSRF token'
			);
		}

		match ($this->type) {
			'method'    => $this->authenticate($auth),
			'challenge' => $this->verify($auth),
			default     => throw new NotFoundException(
				message: 'Invalid login route type "' . $this->type . '"'
			)
		};
		return [];
	}

	protected function verify(Auth $auth): void
	{
		$status = $auth->status();

		// If no challenge is active (e.g. it expired in a previous request),
		// go back to the login form instead of throwing an error
		if ($status->state() !== State::Pending) {
			Panel::go('login');
		}

		if (
			$this->name !== null &&
			$status->challenge() !== $this->name
		) {
			throw new NotFoundException(
				message: 'Login challenge "' . $this->name . '" is not active'
			);
		}

		try {
			$auth->verifyChallenge($this->request->get('code'));
		} catch (Throwable $e) {
			// If the challenge was destroyed during verification
			// (i.e. it timed out and the session was cleared),
			// go back to the login form
			if ($auth->status()->state() !== State::Pending) {
				Panel::go('login');
			}

			throw $e;
		}

		Panel::go($this->kirby->panel()->home()->url());
	}
}
