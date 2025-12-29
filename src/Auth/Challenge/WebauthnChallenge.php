<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Cms\User;
use Kirby\Data\Json;
use Kirby\Exception\InvalidArgumentException;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;
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
	public function create(): null
	{
		ByteBuffer::$useBase64UrlEncoding = true;

		$webauthn     = $this->webauthn();
		$credentials  = $this->credentials();
		$credentialId = array_column($credentials, 'id');
		$allow        = array_map(
			fn ($id) => ByteBuffer::fromBase64Url($id),
			$credentialId
		);

		$options = $webauthn->getGetArgs($allow);
		// ensure rpId is present (some clients fail without it)
		$options->publicKey->rpId ??= $this->rpId();

		// wrap and normalize to array for the client
		$publicKey = Json::decode(Json::encode($options->publicKey), true);
		$options   = ['publicKey' => $publicKey];
		$challenge = $this->base64UrlEncode(
			$webauthn->getChallenge()->getBinaryString()
		);

		// persist challenge and options for the login form
		$this->kirby->session()->set(
			'kirby.challenge.data',
			[
				...$options,
				'challenge' => $challenge
			]
		);

		return null;
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
		// allow JSON/string payloads from the client
		if (is_string($code) === true) {
			$decoded = Json::decode($code, true);
			if (is_array($decoded) === true) {
				$code = $decoded;
			}
		}

		if ($code instanceof \stdClass) {
			$code = (array)$code;
		}

		if (is_array($code) === false) {
			return false;
		}

		ByteBuffer::$useBase64UrlEncoding = true;

		$session = $this->kirby->session();
		$data    = $session->pull('kirby.challenge.data');

		if (
			is_array($data) === false ||
			($data['challenge'] ?? null) === null
		) {
			throw new InvalidArgumentException(
				key: 'webauthn.challenge.missing',
				fallback: 'The passkey challenge is missing or expired'
			);
		}

		$id = $code['id'] ?? null;

		if (is_string($id) === false) {
			throw new InvalidArgumentException(
				key: 'webauthn.id.missing',
				fallback: 'Passkey id is missing'
			);
		}

		$credentials = $this->credentials();
		$credential  = null;

		foreach ($credentials as $entry) {
			if (($entry['id'] ?? null) === $id) {
				$credential = $entry;
				break;
			}
		}

		if ($credential === null) {
			throw new InvalidArgumentException(
				key: 'webauthn.id.invalid',
				fallback: 'Passkey could not be found'
			);
		}

		$clientDataJSON    = $this->base64UrlDecode($code['clientDataJSON'] ?? null);
		$authenticatorData = $this->base64UrlDecode($code['authenticatorData'] ?? null);
		$signature         = $this->base64UrlDecode($code['signature'] ?? null);
		$rawId             = $this->base64UrlDecode($code['rawId'] ?? null);
		$userHandle        = $code['userHandle'] ?? null;
		$publicKey         = $credential['publicKey'] ?? null;
		$counter           = $credential['counter'] ?? null;

		if (is_string($publicKey) === false) {
			throw new InvalidArgumentException(
				key: 'webauthn.publicKey.missing',
				fallback: 'Passkey is incomplete'
			);
		}

		// ensure the credential IDs match the stored ID
		if ($rawId !== $this->base64UrlDecode($credential['id'])) {
			throw new InvalidArgumentException(
				key: 'webauthn.id.invalid',
				fallback: 'Passkey id does not match'
			);
		}

		$challenge = $this->base64UrlDecode($data['challenge']);
		$webauthn  = $this->webauthn();

		try {
			$webauthn->processGet(
				$clientDataJSON,
				$authenticatorData,
				$signature,
				$publicKey,
				$challenge,
				$counter,
				true,
				true
			);
		} catch (WebAuthnException $e) {
			throw new InvalidArgumentException(
				key: 'webauthn.verify.failed',
				fallback: $e->getMessage(),
				previous: $e
			);
		}

		// update the signature counter
		$newCounter = $webauthn->getSignatureCounter();

		if ($newCounter !== null) {
			foreach ($credentials as &$entry) {
				if (($entry['id'] ?? null) === $id) {
					$entry['counter'] = $newCounter;
					break;
				}
			}

			$this->user->changeSecret('webauthn', $credentials);
		}

		return true;
	}

	protected function base64UrlDecode(string|null $data): string
	{
		$data ??= '';
		$padded = strtr($data, '-_', '+/');
		$padLen = (4 - strlen($padded) % 4) % 4;
		$padded .= str_repeat('=', $padLen);

		$decoded = base64_decode($padded, true);

		if ($decoded === false) {
			throw new InvalidArgumentException(
				key: 'webauthn.data.invalid',
				fallback: 'Invalid passkey data'
			);
		}

		return $decoded;
	}

	protected function base64UrlEncode(string $data): string
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	protected function credentials(): array
	{
		$credentials = $this->user->secret('webauthn');

		return $credentials === null || is_array($credentials) === false
			? []
			: array_values($credentials);
	}

	protected function rpId(): string
	{
		return parse_url($this->kirby->site()->url(), PHP_URL_HOST) ?? 'localhost';
	}

	protected function webauthn(): WebAuthn
	{
		return new WebAuthn(
			$this->kirby->site()->title()->value(),
			$this->rpId(),
			['none']
		);
	}
}
