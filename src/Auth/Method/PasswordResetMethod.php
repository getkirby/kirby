<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Component;
use SensitiveParameter;

/**
 * Password-reset flow that triggers a challenge
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PasswordResetMethod extends CodeMethod
{
	public function authenticate(
		string|null $email,
		#[SensitiveParameter]
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
				'icon'  => $this->icon(),
				'label' => static::i18n('login.method.password-reset.submit'),
			],
		);
	}

	public function icon(): string
	{
		return 'question';
	}

	public function isEnabled(): bool
	{
		// don't allow to circumvent 2FA by 1FA code method
		$this->isWithoutAny2FA();

		return true;
	}

	public function settings(User $user): array
	{
		return [];
	}
}
