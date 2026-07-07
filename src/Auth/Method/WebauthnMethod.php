<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Exception\LoginNotPermittedException;
use Kirby\Auth\Method;
use Kirby\Auth\Service\Webauthn;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Exception\UserNotFoundException;
use Kirby\Panel\Ui\Button;
use Kirby\Panel\Ui\Component;
use Kirby\Toolkit\I18n;
use SensitiveParameter;

/**
 * Authenticates a user via a discoverable passkey
 * without requiring an email address upfront.
 * The credential id in the assertion identifies the user.
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class WebauthnMethod extends Method
{
	/**
	 * Session key for the pending login challenge
	 */
	protected string $sessionKey = 'kirby.webauthn.login';

	/**
	 * Verifies the WebAuthn assertion, identifies the user
	 * via the user handle and logs them in
	 */
	public function authenticate(
		string|null $email,
		#[SensitiveParameter]
		string|null $password = null,
		bool $long = false
	): User|Status {
		$kirby = $this->auth->kirby();

		// consume the single-use challenge up front so a failed attempt
		// cannot be retried against the same challenge
		$challenge = $kirby->session()->pull($this->sessionKey);

		// run identification and verification inside the shared rate-limit
		// envelope. The user is only known from the credential, so the
		// limit is keyed by IP; every failure (unknown user or invalid
		// assertion) is tracked, jittered and reported as a generic error.
		return $this->auth->guard(
			email: null,
			attempt: function () use ($password, $challenge, $long) {
				$user     = $this->findUser($password);
				$webauthn = Webauthn::for($user);

				$result = $webauthn->verifyLogin(
					$user->secret('webauthn') ?? [],
					$password,
					$challenge
				);

				if ($result['counter'] !== null) {
					$user->changeSecret('webauthn', $result['credentials']);
				}

				$user->loginPasswordless([
					'createMode' => 'cookie',
					'long'       => $long
				]);

				return $user;
			},
			fallback: new LoginNotPermittedException()
		);
	}

	/**
	 * Identifies the user behind the assertion
	 *
	 * @throws \Kirby\Exception\UserNotFoundException
	 */
	protected function findUser(mixed $payload): User
	{
		$kirby   = $this->auth->kirby();
		$payload = json_decode(is_string($payload) ? $payload : '{}', true) ?? [];
		$handle  = $payload['user'] ?? null;

		if (is_string($handle) === true && $handle !== '') {
			$id   = Webauthn::site($kirby)->decode($handle);
			$user = $kirby->user($id);

			if ($user !== null) {
				return $user;
			}
		}

		throw new UserNotFoundException('-');
	}

	/**
	 * Generates a site-wide WebAuthn challenge with an empty allow list
	 * so the authenticator presents all available passkeys to the user
	 */
	public function form(): Component
	{
		$kirby   = $this->auth->kirby();
		$options = Webauthn::site($kirby)->loginOptions([]);

		$kirby->session()->set($this->sessionKey, $options['challenge']);

		return new Component(
			component: 'k-login-webauthn-method-form',
			publicKey: $options,
		);
	}

	public static function icon(): string
	{
		return 'fingerprint';
	}

	public static function isUsingChallenges(
		Auth $auth,
		array $options = []
	): bool {
		return false;
	}

	public static function settings(User $user): array
	{
		return [
			new Button(
				icon:   static::icon(),
				text:   I18n::translate('login.webauthn.label'),
				drawer: $user->panel()->url(true) . '/security/method/webauthn',
			)
		];
	}
}
