<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\User;
use chillerlan\Authenticator\Authenticator as TOTP;

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
		$otp = new TOTP();
		$otp->setSecret($user->totp());
		return $otp->verify($code);
	}
}
