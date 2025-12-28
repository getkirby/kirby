<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Cms\User;
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

	public function __construct(
		protected User $user,
		protected string $mode,
		protected int $timeout
	) {
		$this->kirby = $user->kirby();
	}

	/**
	 * Generates a random one-time auth code and returns that code
	 * for later verification
	 *
	 * @param array $options Details of the challenge request:
	 *                       - 'mode': Purpose of the code ('login', 'password-reset' or '2fa')
	 *                       - 'timeout': Number of seconds the code will be valid for
	 * @return string|null The generated and sent code or `null` in case
	 *                     there was no code to generate by this algorithm
	 */
	abstract public function create(array $options): string|null;

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
	 * Returns the timeout duration of the challenge
	 */
	public function timeout(): int
	{
		return $this->timeout;
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
