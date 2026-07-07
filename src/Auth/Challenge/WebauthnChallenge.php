<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Auth\Pending;
use Kirby\Auth\Service\Webauthn;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Button;
use Kirby\Panel\Ui\Component;
use SensitiveParameter;

/**
 * Verifies a WebAuthn assertion as a second factor
 * when the user is already identified (e.g. after password).
 * Users first have to register passkeys in their security settings.
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class WebauthnChallenge extends Challenge
{

	/**
	 * Generates WebAuthn login options for the user's registered passkeys
	 * and stores the challenge for later verification
	 */
	public function create(): Pending
	{
		$credentials = $this->user->secret('webauthn') ?? [];
		$webauthn    = Webauthn::for($this->user);
		$options     = $webauthn->loginOptions($credentials);

		return new Pending(
			public: $options,
			secret: $options['challenge']
		);
	}

	public function form(Pending $pending): Component
	{
		return new Component(
			component: 'k-login-webauthn-challenge-form',
			submit: [
				'icon'  => 'fingerprint',
				'label' => static::i18n('login.method.webauthn.label'),
			],
			publicKey: $pending->public(),
			user:      $this->user->email(),
		);
	}

	public static function icon(): string
	{
		return 'fingerprint';
	}

	/**
	 * Checks whether the user has registered any passkeys
	 */
	public static function isAvailable(User $user, string $mode = 'login'): bool
	{
		$credentials = $user->secret('webauthn');
		return is_array($credentials) === true && $credentials !== [];
	}

	/**
	 * WebAuthn signs a one-time cryptographic nonce, so the challenge
	 * must be invalidated after any assertion to prevent a failed
	 * signature from being replayed within the timeout window
	 */
	public function isSingleUse(): bool
	{
		return true;
	}

	public static function settings(User $user): array
	{
		return [
			new Button(
				icon:   static::icon(),
				text:   static::i18n('login.webauthn.label'),
				drawer: $user->panel()->url(true) . '/security/method/webauthn',
			)
		];
	}

	/**
	 * Verifies the WebAuthn assertion and updates the credential counter
	 */
	public function verify(
		#[SensitiveParameter]
		mixed $input,
		Pending $data
	): bool {
		$credentials = $this->user->secret('webauthn') ?? [];
		$webauthn    = Webauthn::for($this->user);
		$result      = $webauthn->verifyLogin($credentials, $input, $data->secret());

		if ($result['counter'] !== null) {
			$this->user->changeSecret('webauthn', $result['credentials']);
		}

		return true;
	}
}
