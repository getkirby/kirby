<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Cms\Auth\Status;
use Kirby\Cms\User;
use Kirby\Http\Idn;

/**
 * Authenticates a user with email + password
 * and optionally triggers a 2FA challenge.
 */
class PasswordMethod extends Method
{
	public function __construct(
		string $type,
		protected bool $twoFactor = false
	) {
		parent::__construct(type: $type);
	}

	public function attempt(
		string $email,
		string|null $password = null,
		bool $long = false,
		string $mode = 'login'
	): User|Status|null {
		// not applicable without a password
		if ($password === null) {
			return null;
		}

		$email = Idn::decodeEmail($email);

		// validate credentials
		$user = $this->auth()->validatePassword($email, $password);

		// two-factor flow: create a challenge after password validation
		if ($this->twoFactor === true && $mode === 'login') {
			return $this->auth()->createChallenge($email, $long, '2fa');
		}

		// log the user in with a cookie-based session
		$user->loginPasswordless([
			'createMode' => 'cookie',
			'long'       => $long === true
		]);

		$this->auth()->setUser($user);

		return $user;
	}

	public static function factory(
		string $type,
		array $options = []
	): static {
		return new static(
			type:      $type,
			twoFactor: $options['2fa'] ?? false
		);
	}
}
