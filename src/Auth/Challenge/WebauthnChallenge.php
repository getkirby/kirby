<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Auth\Service\Webauthn;
use Kirby\Cms\User;
use SensitiveParameter;

/**
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class WebauthnChallenge extends Challenge
{
	protected Webauthn $webauthn;

	public function __construct(
		User $user,
		string $mode,
		int|null $timeout = null,
	) {
		parent::__construct(
			user: $user,
			mode: $mode,
			timeout: $timeout
		);

		$this->webauthn = Webauthn::for($user);
	}

	public function create(): null
	{
		$credentials = $this->credentials();
		$options     = $this->webauthn->loginOptions($credentials);

		// persist challenge and options for the login form
		$this->kirby->session()->set('kirby.challenge.data', $options);

		return null;
	}

	protected function credentials(): array
	{
		return $this->user->secret('webauthn') ?? [];
	}

	public static function form(): string
	{
		return 'k-login-webauthn-challenge';
	}

	public static function isAvailable(User $user, string $mode = 'login'): bool
	{
		return $user->secret('webauthn') !== null;
	}

	public static function settings(User $user): array
	{
		return [
			[
				'text'   => 'Secret codes',
				'icon'   => 'fingerprint',
				'dialog' => $user->panel()->url(true) . '/webauthn',
			],
		];
	}

	public function verify(
		#[SensitiveParameter]
		mixed $code
	): bool {
		$credentials = $this->credentials();
		$data        = $this->kirby->session()->pull('kirby.challenge.data');

		$result = $this->webauthn->verifyLogin(
			$credentials,
			$code,
			$data['challenge']
		);

		if ($result['counter'] !== null) {
			$this->user->changeSecret('webauthn', $result['credentials']);
		}

		return true;
	}

}
