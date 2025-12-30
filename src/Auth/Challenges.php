<?php

namespace Kirby\Auth;

use Kirby\Auth\Exception\ChallengeTimeoutException;
use Kirby\Auth\Exception\InvalidChallengeCodeException;
use Kirby\Auth\Exception\UserNotFoundException;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Session\Session;
use Kirby\Toolkit\A;

/**
 * Orchestrates selection and creation of authentication challenges
 *
 * @package   Kirby Auth
 * @since     6.0.0
 */
class Challenges
{
	public function __construct(
		protected App $kirby
	) {
	}

	public function available(User $user, string $mode): array
	{
		return A::filter(
			$this->enabled(),
			fn ($type) => $this->class($type)::isAvailable($user, $mode)
		);
	}

	/**
	 * Returns the challenge handler class for the provided type
	 */
	public function class(string $type): string
	{
		if (
			($class = Auth::$challenges[$type] ?? null) &&
			is_subclass_of($class, Challenge::class) === true
		) {
			return $class;
		}

		return throw new NotFoundException(
			message: 'No valid auth challenge class found for type: ' . $type
		);
	}

	public function clear(): void
	{
		$session = $this->kirby->session();
		$session->remove('kirby.challenge.code');
		$session->remove('kirby.challenge.email');
		$session->remove('kirby.challenge.mode');
		$session->remove('kirby.challenge.timeout');
		$session->remove('kirby.challenge.type');
		$session->remove('kirby.challenge.data');
	}

	/**
	 * Creates the first available challenge for the user
	 * and stores state in the session
	 */
	public function create(
		Session $session,
		string $email,
		string $mode,
	): Challenge|null {
		// rate-limit the number of challenges for DoS/DDoS protection
		$this->limits()->ensure($email);
		$this->limits()->track($email);

		// try to find the provided user
		$user = $this->kirby->users()->find($email);

		if ($user === null) {
			throw new UserNotFoundException(name: $email);
		}

		// try to find a challenge that is available for that user
		if ($challenge = $this->firstAvailable($user, $mode)) {
			$challenge = $this->class($challenge);
			$challenge = new $challenge(user: $user, mode: $mode);
			$code      = $challenge->create();
			$timeout   = $this->timeout();

			$session->set('kirby.challenge.type', $challenge->type());
			$session->set('kirby.challenge.timeout', time() + $timeout);

			if ($code !== null) {
				$session->set(
					'kirby.challenge.code',
					password_hash($code, PASSWORD_DEFAULT)
				);
			}

			return $challenge;
		}

		return null;
	}

	/**
	 * Returns normalized array of enabled challenges
	 * by the `auth.challenges` config option
	 */
	public function enabled(): array
	{
		return A::wrap(
			$this->kirby->option('auth.challenges', ['totp', 'webauthn', 'email'])
		);
	}

	/**
	 * Checks if an active challenge exists or fails otherwise
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function ensureActiveChallenge(Session $session): string
	{
		// check if we have an active challenge
		$email = $session->get('kirby.challenge.email');
		$type  = $session->get('kirby.challenge.type');

		// if the challenge timed out on the previous request, the
		// challenge data was already deleted from the session, so we can
		// set `challengeDestroyed` to `true` in this response as well;
		// however we must only base this on the email, not the type
		// (otherwise "faked" challenges would be leaked)
		if (is_string($email) !== true || is_string($type) !== true) {
			throw new InvalidArgumentException(
				details: ['challengeDestroyed' => is_string($email) !== true],
				fallback: 'No authentication challenge is active'
			);
		}

		return $email;
	}

	protected function ensureNotTimeout(Session $session): int|null
	{
		// time-limiting; check this early so that we can
		// destroy the session no matter if the user exists
		// (avoids leaking user information to attackers)
		$timeout = $session->get('kirby.challenge.timeout');

		// challenge timed out
		if ($timeout !== null && time() > $timeout) {
			throw new ChallengeTimeoutException();
		}

		return $timeout;
	}

	public function firstAvailable(User $user, string $mode): string|null
	{
		return $this->available($user, $mode)[0] ?? null;
	}

	protected function limits(): Limits
	{
		return $this->kirby->auth()->limits();
	}

	public function timeout(): int|null
	{
		return $this->kirby->option('auth.challenge.timeout', 10 * 60);
	}

	/**
	 * Verifies and return the current challenge
	 */
	public function verify(
		Session $session,
		array|string|null $code
	): Challenge {
		// ensu we have an active challenge for a valid user
		$timeout = $this->ensureNotTimeout($session);
		$email   = $this->ensureActiveChallenge($session);
		$user    = $this->kirby->users()->find($email);

		if ($user === null) {
			throw new UserNotFoundException(name: $email);
		}

		// rate-limiting
		$this->limits()->ensure($email);

		//
		$type      = $session->get('kirby.challenge.type');
		$challenge = $this->class($type);

		if ($challenge === null) {
			throw new LogicException(
				message: 'Invalid authentication challenge: ' . $type
			);
		}

		$user = $this->kirby->user($email);

		if ($user === null) {
			throw new UserNotFoundException(name: $email);
		}

		$challenge = new $challenge(
			user:    $user,
			mode:    $session->get('kirby.challenge.mode'),
			timeout: $session->get('kirby.challenge.timeout')
		);

		if ($challenge->verify($code) !== true) {
			throw new InvalidChallengeCodeException();
		}

		return $challenge;
	}
}
