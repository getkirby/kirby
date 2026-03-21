<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Component;

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
		string|null $email,
		string|null $password = null,
		bool $long = false
	): Status {
		return $this->auth->createChallenge(
			mode: 'password-reset',
			email: $email,
			long:  false, // should always use a short-lived session
		);
	}

	public function form(): Component
	{
		return new Component(
			component: 'k-login-password-reset-method-form',
			submit: [
				'icon'  => static::icon(),
				'label' => static::i18n('login.method.password-reset.submit'),
			],
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
