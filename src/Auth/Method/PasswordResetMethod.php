<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Auth\Status;

/**
 * Password-reset flow that triggers a challenge
 */
class PasswordResetMethod extends Method
{
	public function authenticate(
		string $email,
		string|null $password = null,
		bool $long = false
	): Status {
		return $this->auth()->createChallenge($email, $long, 'password-reset');
	}

	public static function form(): string
	{
		return 'k-login-password-method';
	}

	public function icon(): string
	{
		return 'question';
	}
}
