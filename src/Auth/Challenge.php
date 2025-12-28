<?php

namespace Kirby\Auth;

use Kirby\Auth\Exception\UserNotFoundException;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\User;
use Kirby\Session\Session;
use SensitiveParameter;

/**
 * Base class for authentication challenges
 * that create and verify one-time auth codes
 *
 * @package   Kirby Auth
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class Challenge
{
	protected App $kirby;
	protected int $timeout;

	public function __construct(
		protected User $user,
		protected string $mode,
		protected string $type,
		int|null $timeout = null,
	) {
		$this->kirby   = $user->kirby();
		$this->timeout = $timeout ?? $this->kirby->option('auth.challenge.timeout', 10 * 60);
	}

	/**
	 * Generates a random one-time auth code and returns that code
	 * for later verification
	 *
	 * @return string|null The generated and sent code or `null` in case
	 *                     there was no code to generate by this algorithm
	 */
	abstract public function create(): string|null;

	final public static function for(
		string $type,
		User $user,
		string $mode
	): static|null {
		if (
			($handler = static::handler($type) ?? null) &&
			$handler::isAvailable($user, $mode) === true
		) {
			return new $handler(
				user: $user,
				mode: $mode,
				type: $type
			);
		}

		return null;
	}

	final public static function from(Session $session): static|null
	{
		$type    = $session->get('kirby.challenge.type');
		$handler = static::handler($type);

		if ($handler === null) {
			return null;
		}

		$email = $session->get('kirby.challenge.email');
		$user  = App::instance()->users()->find($email);

		if ($user === null) {
			throw new UserNotFoundException(name: $email);
		}

		return new $handler(
			user:    $user,
			mode:    $session->get('kirby.challenge.mode'),
			timeout: $session->get('kirby.challenge.timeout'),
			type:    $session->get('kirby.challenge.type'),
		);
	}

	/**
	 * Returns the challenge handler class for the provided type
	 */
	final public static function handler(string $type): string|null
	{
		if (
			($class = Auth::$challenges[$type] ?? null) &&
			is_subclass_of($class, self::class) === true
		) {
			return $class;
		}

		return null;
	}

	/**
	 * Checks whether the challenge is available
	 * for the passed user and purpose
	 *
	 * @param \Kirby\Cms\User $user User the code will be generated for
	 * @param 'login'|'password-reset'|'2fa' $mode Purpose of the code
	 */
	abstract public static function isAvailable(User $user, string $mode): bool;

	/**
	 * Returns the purpose of the challenge
	 * @return 'login'|'password-reset'|'2fa'
	 */
	public function mode(): string
	{
		return $this->mode;
	}

	/**
	 * Returns the number of seconds the code will be valid for
	 */
	public function timeout(): int
	{
		return $this->timeout;
	}

	public function type(): string
	{
		return $this->type;
	}

	/**
	 * Returns the user the challenge belongs to
	 */
	public function user(): User
	{
		return $this->user;
	}

	/**
	 * Verifies the provided code against;
	 * default implementation that checks the code that was
	 * returned from the `create()` method
	 */
	public function verify(
		#[SensitiveParameter]
		string $code
	): bool {
		$hash = $this->kirby->session()->get('kirby.challenge.code');

		if (is_string($hash) !== true) {
			return false;
		}

		// normalize the formatting in the user-provided code
		$code = str_replace(' ', '', $code);

		return password_verify($code, $hash);
	}
}
