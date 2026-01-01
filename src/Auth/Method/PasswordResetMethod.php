<?php

namespace Kirby\Auth\Method;

use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;

/**
 * Password-reset flow that triggers a challenge
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PasswordResetMethod extends CodeMethod
{
	public function authenticate(
		string $email,
		string|null $password = null,
		bool $long = false
	): Status {
		return $this->auth->createChallenge(
			mode: 'password-reset',
			email: $email,
			long:  false,
		);
	}

	public static function icon(): string
	{
		return 'question';
	}

	public static function isAvailable(Auth $auth, array $options = []): bool
	{
		// don't allow to circumvent 2FA by 1FA code method
		if (static::isWithoutAny2FA($auth) === false) {
			return false;
		}

		return true;
	}
}
