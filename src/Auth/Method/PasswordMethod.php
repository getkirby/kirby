<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Cms\Auth\Status;
use Kirby\Cms\User;

/**
 * Authenticates a user with email + password
 * and optionally triggers a 2FA challenge.
 */
class PasswordMethod extends Method
{
	public function attempt(
		string $email,
		string|null $password = null,
		bool $long = false,
		string $mode = 'login'
	): User|Status|null {
		if ($password === null) {
			return null;
		}

		$user = $this->auth()->validatePassword($email, $password);

		// two-factor flow: create a challenge after password validation
		if ($mode === 'login' && $this->has2FA($user) === true) {
			return $this->auth()->createChallenge($email, $long, '2fa');
		}

		// log the user in with a cookie-based session
		$user->loginPasswordless([
			'createMode' => 'cookie',
			'long'       => $long === true
		]);

		return $user;
	}

	protected function has2FA(User $user): bool
	{
		return ($this->options['2fa'] ?? false) === true;
	}
}
