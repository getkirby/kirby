<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\User;

/**
 * Template class for authentication challenges
 * that create and verify one-time auth codes
 *
 * @package   Kirby Cms
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class Challenge
{
	/**
	 * Checks whether the challenge is available
	 * for the passed user and purpose
	 *
	 * @param \Kirby\Cms\User $user User the code will be generated for
	 * @param string $mode Purpose of the code ('login', 'reset' or '2fa')
	 * @return bool
	 */
	abstract public static function isAvailable(User $user, string $mode): bool;

	/**
	 * Generates a random one-time auth code and returns that code
	 * for later verification
	 *
	 * @param \Kirby\Cms\User $user User to generate the code for
	 * @param array $options Details of the challenge request:
	 *                       - 'mode': Purpose of the code ('login', 'reset' or '2fa')
	 *                       - 'timeout': Number of seconds the code will be valid for
	 * @return string|null The generated and sent code or `null` in case
	 *                     there was no code to generate by this algorithm
	 */
	abstract public static function create(User $user, array $options): ?string;

	/**
	 * Verifies the provided code against the created one;
	 * default implementation that checks the code that was
	 * returned from the `create()` method
	 *
	 * @param \Kirby\Cms\User $user User to check the code for
	 * @param string $code Code to verify
	 * @return bool
	 */
	public static function verify(User $user, string $code): bool
	{
		$hash = $user->kirby()->session()->get('kirby.challenge.code');
		if (is_string($hash) !== true) {
			return false;
		}

		// normalize the formatting in the user-provided code
		$code = str_replace(' ', '', $code);

		return password_verify($code, $hash);
	}
}
