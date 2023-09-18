<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\User;
use Kirby\Toolkit\Totp;

class TotpChallenge extends Challenge
{
	/**
	 * Checks whether TOTP is activated for the user
	 */
	public static function isAvailable(User $user, string $mode): bool
	{
		 return $user->totp() !== null;
	}

	/**
	 * Since TOTP codes are generated automatically,
	 * don't return a code here
	 */
	public static function create(User $user, array $options): string|null
	{
		return null;
	}

	/**
	 * Check TOTP code
	 */
	public static function verify(User $user, string $code): bool
	{
		$totp = new Totp($user->totp());
		return $totp->verify($code);
	}
}
