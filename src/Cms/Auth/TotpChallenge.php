<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\User;
use Kirby\Toolkit\Totp;

/**
 * Verifies one-time time-based auth codes
 * that are generated with an authenticator app.
 * Users first have to set up time-based codes
 * (storing the TOTP secret in their user account).
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
	 * Checks whether time-based codes are set up for user
	 */
	public static function isAvailable(User $user, string $mode): bool
	{
		return $user->totp() !== null;
	}

	/**
	 * Since TOTP codes are generated automatically, return null
	 */
	public static function create(User $user, array $options): string|null
	{
		return null;
	}

	/**
	 * Verify if code is current, previous or next TOTP code
	 */
	public static function verify(User $user, string $code): bool
	{
		$secret = $user->totp();
		$totp   = new Totp($secret);
		return $totp->verify($code);
	}
}
