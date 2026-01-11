<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Auth\Pending;
use Kirby\Cms\User;
use Kirby\Toolkit\Totp;
use SensitiveParameter;

/**
 * Verifies one-time time-based auth codes
 * that are generated with an authenticator app.
 * Users first have to set up time-based codes
 * (storing the TOTP secret in their user account).
 *
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TotpChallenge extends Challenge
{
	/**
	 * The user's app will generate the code, we only verify it
	 */
	public function create(): null
	{
		return null;
	}

	/**
	 * Checks whether the user has set up TOTP
	 */
	public static function isAvailable(User $user, string $mode = 'login'): bool
	{
		return $user->secret('totp') !== null;
	}

	/**
	 * Verifies the input is the current, previous or next TOTP code
	 */
	public function verify(
		#[SensitiveParameter]
		mixed $input,
		Pending $data
	): bool {
		$secret = $this->user->secret('totp');
		return (new Totp($secret))->verify($input);
	}
}
