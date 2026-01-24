<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Login;

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

	public function form(): Login
	{
		return new Login(
			for:          $this,
			component:    'k-login-email-password-form',
			hasPassword:  false,
			hasRemember:  false,
			submitButton: [
				'icon' => static::icon(),
				'text' => $this->i18n('login.reset')
			]
		);
	}

	public static function icon(): string
	{
		return 'question';
	}

	public static function isEnabled(Auth $auth, array $options = []): bool
	{
		// don't allow to circumvent 2FA by 1FA code method
		static::isWithoutAny2FA($auth);

		return true;
	}

	public static function settings(User $user): array
	{
		return [];
	}
}
