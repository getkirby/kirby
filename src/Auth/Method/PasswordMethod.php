<?php

namespace Kirby\Auth\Method;

use InvalidArgumentException;
use Kirby\Auth\Method;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
use Kirby\Cms\User;
use SensitiveParameter;

/**
 * Authenticates a user with email + password
 * and optionally triggers a 2FA challenge
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
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
		string $email,
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

	/**
	 * Checks whether a second-factor is required
	 */
	protected function has2FA(User $user): bool
	{
		return static::isUsingChallenges(
			$this->auth,
			$this->options
		);
	}

	public static function isUsingChallenges(
		Auth $auth,
		array $options = []
	): bool {
		$option = $options['2fa'] ?? null;

		if ($option === true) {
			return true;
		}

		return false;
	}

	public static function settings(User $user): array
	{
		return [
			[
				'icon'     => 'key',
				'text'     => static::i18n('password'),
				'dialog'   => $user->panel()->url(true) . '/changePassword',
				'disabled' => !$user->permissions()->can('changePassword'),
			]
		];
	}
}
