<?php

namespace Kirby\Auth\Method;

use InvalidArgumentException;
use Kirby\Auth\Method;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Button;
use Kirby\Panel\Ui\Component;
use SensitiveParameter;

/**
 * Authenticates a user with email + password
 * and optionally triggers a 2FA challenge
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PasswordMethod extends Method
{
	/**
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is missing
	 */
	public function authenticate(
		string|null $email,
		#[SensitiveParameter]
		string|null $password = null,
		bool $long = false
	): User|Status {
		if ($password === null) {
			throw new InvalidArgumentException(
				message: 'Missing password'
			);
		}

		$user = $this->auth->validatePassword($email, $password);

		// two-factor flow: create a challenge after password validation
		if ($this->has2FA($user) === true) {
			return $this->auth->createChallenge(
				mode:  '2fa',
				email: $email,
				long:  $long,
			);
		}

		// log the user in with a cookie-based session
		$user->loginPasswordless([
			'createMode' => 'cookie',
			'long'       => $long === true
		]);

		return $user;
	}

	public function form(): Component
	{
		return new Component(
			component: 'k-login-password-method-form',
			submit: [
				'icon'  => static::icon(),
				'label' => static::i18n('login'),
			],
		);
	}

	/**
	 * Checks whether a second factor is required for this user
	 */
	public function has2FA(User $user): bool
	{
		// enforced: every user needs a second factor,
		// even if they have not set one up yet
		if (($this->options['2fa'] ?? null) === true) {
			return true;
		}

		// otherwise only challenge users who have any
		// second factor set up (= available) for them
		return $this->auth->challenges()->hasAvailable($user, '2fa');
	}

	public static function settings(User $user): array
	{
		return [
			new Button(
				icon:     static::icon(),
				text:     static::i18n('password'),
				dialog:   $user->panel()->url(true) . '/changePassword',
				disabled: !$user->permissions()->can('changePassword'),
			)
		];
	}
}
