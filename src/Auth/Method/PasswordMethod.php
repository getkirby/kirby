<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Exception\LogicException;

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
		if ($mode === 'login' && $this->has2FA($user, $mode) === true) {
			return $this->auth()->createChallenge($email, $long, '2fa');
		}

		// log the user in with a cookie-based session
		$user->loginPasswordless([
			'createMode' => 'cookie',
			'long'       => $long === true
		]);

		return $user;
	}

	protected function has2FA(User $user, string $mode): bool
	{
		$option = $this->options['2fa'] ?? null;

		if ($option !== true && $option !== 'optional') {
			return false;
		}

		// get first available challenge
		$challenge = $this->auth()->challenges()->available($user, $mode);

		// if any challenge is available, use 2FA
		if ($challenge !== null) {
			return true;
		}

		// if no challenge is available, but enforced via config => fail
		if ($option === true) {
			throw new LogicException(
				message: '2-factor authentication required but not challenge available'
			);
		}

		return false;
	}

	public function icon(): string
	{
		return 'key';
	}

	public static function settings(User $user): array
	{
		return [
			[
				'text'     => 'Password',
				'icon'     => 'key',
				'disabled' => !$user->permissions()->can('changePassword'),
				'dialog'   => $user->panel()->url(true) . '/changePassword'
			]
		];
	}
}
