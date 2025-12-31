<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
use Kirby\Exception\InvalidArgumentException;

/**
 * Passwordless login via one-time code
 * or any other available challenge
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class CodeMethod extends Method
{
	public function authenticate(
		string $email,
		string|null $password = null,
		bool $long = false
	): Status {
		// no password required; directly create challenge
		return $this->auth->createChallenge(
			mode: 'login',
			email: $email,
			long:  $long,
		);
	}

	public static function isAvailable(Auth $auth, array $options = []): bool
	{
		// don't allow to circumvent 2FA by 1FA code method
		if (static::isWithoutAny2FA($auth) === false) {
			return false;
		}

		// only one code-based mode can be active at once
		if (static::isWithoutPasswordReset($auth) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Don't allow to circumvent 2FA by 1FA code method
	 */
	protected static function isWithoutAny2FA(Auth $auth): bool
	{
		// don't allow to circumvent 2FA by 1FA code method
		if ($auth->methods()->hasAnyWith2FA() === true) {
			if ($auth->kirby()->option('debug') === true) {
				throw new InvalidArgumentException(
					message: 'The "' . static::type() . '" login method cannot be enabled when 2FA is required'
				);
			}

			return false;
		}

		return true;
	}

	/**
	 * Only one code-based mode can be active at once
	 */
	protected static function isWithoutPasswordReset(Auth $auth): bool
	{
		if ($auth->methods()->has('password-reset') === true) {
			if ($auth->kirby()->option('debug') === true) {
				throw new InvalidArgumentException(
					message: 'The "code" and "password-reset" login methods cannot be enabled together'
				);
			}

			return false;
		}

		return true;
	}

}
