<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Cms\Auth\Status;

/**
 * Passwordless login via one-time code (challenge)
 */
class CodeMethod extends Method
{
	public function attempt(
		string $email,
		string|null $password = null,
		bool $long = false,
		string $mode = 'login'
	): Status|null {
		if ($mode !== 'login') {
			return null;
		}

		// no password required; directly create a challenge
		return $this->auth()->createChallenge($email, $long, 'login');
	}
}
