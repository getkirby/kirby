<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Auth\Status;

/**
 * Password-reset flow that triggers a challenge
 */
class PasswordResetMethod extends Method
{
	public function attempt(
		string $email,
		string|null $password = null,
		bool $long = false,
		string $mode = 'password-reset'
	): Status|null {
		if ($mode !== 'password-reset') {
			return null;
		}

		return $this->auth()->createChallenge($email, $long, 'password-reset');
	}
}
