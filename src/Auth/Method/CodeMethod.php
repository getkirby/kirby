<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Method;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\Button;

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

	public static function icon(): string
	{
		return 'hashtag';
	}

	public static function isEnabled(Auth $auth, array $options = []): bool
	{
		// don't allow to circumvent 2FA by 1FA code method
		static::isWithoutAny2FA($auth);

		// only one code-based mode can be active at once
		static::isWithoutPasswordReset($auth);

		return true;
	}

	public static function isUsingChallenges(
		Auth $auth,
		array $options = []
	): bool {
		return true;
	}

	/**
	 * Don't allow to circumvent 2FA by 1FA code method
	 */
	protected static function isWithoutAny2FA(Auth $auth): bool
	{
		if ($auth->methods()->requires2FA() === true) {
			throw new InvalidArgumentException(
				message: 'The "' . static::type() . '" login method cannot be enabled when 2FA is required'
			);
		}

		return true;
	}

	/**
	 * Only one code-based mode can be active at once
	 */
	protected static function isWithoutPasswordReset(Auth $auth): bool
	{
		if (array_key_exists('password-reset', $auth->methods()->config()) === true) {
			throw new InvalidArgumentException(
				message: 'The "code" and "password-reset" login methods cannot be enabled together'
			);
		}

		return true;
	}

	public static function settings(User $user): array
	{
		return [
			new Button(
				icon: static::icon(),
				text: static::i18n('login.method.code.label'),
				drawer: $user->panel()->url(true) . '/security/method/code'
			)
		];
	}
}
