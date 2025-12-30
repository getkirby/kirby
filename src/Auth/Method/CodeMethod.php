<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Auth\Status;

/**
 * Passwordless login via one-time code
 * or any other first available challenge
 */
class CodeMethod extends Method
{
	public function authenticate(
		string $email,
		string|null $password = null,
		bool $long = false
	): Status {
		// no password required; directly create challenge
		return $this->auth()->createChallenge($email, $long, 'login');
	}

	public static function form(): string
	{
		return 'k-login-password-method';
	}

	public function icon(): string
	{
		return 'hashtag';
	}
}
