<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
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

	public static function form(): string
	{
		return 'k-login-code-challenge';
	}

	/**
	 * Checks whether TOTP is available for the user and purpose
	 */
	public static function isAvailable(User $user, string $mode = 'login'): bool
	{
		// user needs to have a TOTP secret set up
		return $user->secret('totp') !== null;
	}

	public static function settings(User $user): array
	{
		return [
			[
				'text'   => 'Set up authenticator app',
				'icon'   => 'qr-code',
				'dialog' => $user->panel()->url(true) . '/totp/' . (static::isAvailable($user) ? 'disable' : 'enable'),
			],
		];
	}

	/**
	 * Verifies the provided code is the current, previous or next TOTP code
	 */
	public function verify(
		#[SensitiveParameter]
		string $code
	): bool {
		$secret = $this->user->secret('totp');
		return (new Totp($secret))->verify($code);
	}
}
