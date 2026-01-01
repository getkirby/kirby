<?php

namespace Kirby\Auth;

use Kirby\Auth\Challenge\LegacyChallenge;
use Kirby\Auth\Exception\ChallengeTimeoutException;
use Kirby\Auth\Exception\UserNotFoundException;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Challenge as LegacyBaseChallenge;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Session\Session;
use Kirby\Toolkit\A;
use SensitiveParameter;

/**
 * Handler for all auth challenges
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Challenges
{
	/**
	 * Available auth challenge classes
	 * from the core and plugins
	 */
	public static array $challenges = [];

	public function __construct(
		protected Auth $auth,
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
			($class = static::$challenges[$type] ?? null) &&
			(
				is_subclass_of($class, Challenge::class) === true ||
				is_subclass_of($class, LegacyBaseChallenge::class) === true
			)
		) {
			return $class;
		}

		throw new NotFoundException(
			message: 'No auth challenge class for: ' . $type
		);
	}

	public function clear(): void
	{
		$session = $this->kirby->session();
		$session->remove('kirby.challenge.data');
		$session->remove('kirby.challenge.email');
		$session->remove('kirby.challenge.mode');
		$session->remove('kirby.challenge.timeout');
		$session->remove('kirby.challenge.type');

		// @deprecated
		$session->remove('kirby.challenge.code');
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
		$this->auth->limits()->ensure($email);
		$this->auth->limits()->track($email, triggerHook: false);

		// try to find the provided user
		$user = $this->kirby->user($email);

		if ($user === null) {
			$this->kirby->trigger('user.login:failed', ['email' => $email]);
			throw new UserNotFoundException(name: $email);
		}

		// try to find a challenge that is available for that user
		if ($challenge = $this->firstAvailable($user, $mode)) {
			$data      = $challenge->create();
			$timeout   = $this->timeout();

			$session->set('kirby.challenge.type', $challenge->type());
			$session->set('kirby.challenge.timeout', time() + $timeout);

			if ($data !== null) {
				$session->set('kirby.challenge.data', $data->toArray());
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
			$this->kirby->option('auth.challenges', ['totp', 'email'])
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

	/**
	 * Returns the first available auth challenge
	 * for the user and purpose/mode
	 */
	public function firstAvailable(User $user, string $mode): Challenge|null
	{
		$available = $this->available($user, $mode);
		$type      = array_shift($available);
		return $type !== null ? $this->get($type, $user, $mode) : null;
	}

	/**
	 * Returns an instance of the requested auth challenge.
	 * (This is based on the config. You might need to check
	 * yourself if the method should be available in your context)
	 */
	public function get(
		string $type,
		User $user,
		string $mode,
		int|null $timeout = null
	): Challenge {
		$challenge = $this->class($type);
		$timeout ??= $this->timeout();

		if (is_subclass_of($challenge, LegacyBaseChallenge::class) === true) {
			return new LegacyChallenge(
				type:    $type,
				class:   $challenge,
				user:    $user,
				mode:    $mode,
				timeout: $timeout,
			);
		}

		return new $challenge(
			user:    $user,
			mode:    $mode,
			timeout: $timeout
		);
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
		#[SensitiveParameter]
		mixed $input
	): Challenge {
		// ensure we have an active challenge for a valid user
		$timeout = $this->ensureNotTimeout($session);
		$email   = $this->ensureActiveChallenge($session);
		$user    = $this->kirby->user($email);

		if ($user === null) {
			throw new UserNotFoundException(name: $email);
		}

		// rate-limiting
		$this->auth->limits()->ensure($email);

		$type      = $session->get('kirby.challenge.type');
		$mode      = $session->get('kirby.challenge.mode');
		$data      = $session->get('kirby.challenge.data');
		$data      = Pending::from($data ?? []);
		$challenge = $this->get($type, $user, $mode, $timeout);

		if ($challenge->verify($input, $data) !== true) {
			throw new PermissionException(key: 'access.code');
		}

		return $challenge;
	}
}
