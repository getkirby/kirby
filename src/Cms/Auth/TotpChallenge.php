<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\User;
use Kirby\Toolkit\Totp;

/**
 * Verifies one-time time-based auth codes
 * that are generated with an authenticator app.
 * Users first have to set up time-based codes
 * (storing the TOTP secret in their user account).
 * @since 4.0.0
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class TotpChallenge extends Challenge
{
	/**
	 * Checks whether the challenge is available
	 * for the passed user and purpose
	 *
	 * @param \Kirby\Cms\User $user User the code will be generated for
	 * @param 'login'|'password-reset'|'2fa' $mode Purpose of the code
	 */
	public static function isAvailable(User $user, string $mode): bool
	{
		// user needs to have a TOTP secret set up
		return $user->secret('totp') !== null;
	}

	/**
	 * Generates a random one-time auth code and returns that code
	 * for later verification
	 *
	 * @param \Kirby\Cms\User $user User to generate the code for
	 * @param array $options Details of the challenge request:
	 *                       - 'mode': Purpose of the code ('login', 'password-reset' or '2fa')
	 *                       - 'timeout': Number of seconds the code will be valid for
	 * @todo set return type to `null` once support for PHP 8.1 is dropped
	 */
	public static function create(User $user, array $options): string|null
	{
		// the user's app will generate the code, we only verify it
		return null;
	}

	/**
	 * Verifies the provided code against the created one
	 *
	 * @param \Kirby\Cms\User $user User to check the code for
	 * @param string $code Code to verify
	 */
	public static function verify(User $user, string $code): bool
	{
		// verify if code is current, previous or next TOTP code
		$secret = $user->secret('totp');
		$totp   = new Totp($secret);
		return $totp->verify($code);
	}
}
