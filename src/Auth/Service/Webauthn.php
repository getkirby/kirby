<?php

namespace Kirby\Auth\Service;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn as BaseWebauthn;
use lbuchs\WebAuthn\WebAuthnException;
use stdClass;

/**
 * Wrapper for WebAuthn library that keeps base64url handling
 * and common operations in one place
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Webauthn extends BaseWebauthn
{
	public function __construct(
		protected string $id,
		string $title,
		protected string $user = '',
		protected string $email = '',
		protected string $name = '',
		array $formats = ['none']
	) {
		ByteBuffer::$useBase64UrlEncoding = true;
		parent::__construct($title, $id, $formats, true);
	}

	/**
	 * Turns a credential's base64url id into a ByteBuffer
	 */
	protected function bufferId(array $credential): ByteBuffer
	{
		return ByteBuffer::fromBase64Url($credential['id'] ?? '');
	}

	/**
	 * Ensures the stored challenge is present and not empty
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function challenge(mixed $challenge): string
	{
		if (is_string($challenge) === false || $challenge === '') {
			throw new InvalidArgumentException(
				key: 'user.webauthn.challenge'
			);
		}

		return $challenge;
	}

	/**
	 * Finds a stored credential by its id
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function credential(array $credentials, string $id): array
	{
		foreach ($credentials as $credential) {
			if (($credential['id'] ?? null) === $id) {
				return $credential;
			}
		}

		// generic failure so we don't reveal which credential is (un)known
		throw new InvalidArgumentException(
			key: 'user.webauthn.failed'
		);
	}

	/**
	 * Decodes a base64url string into its raw binary form
	 */
	public function decode(string|null $data): string
	{
		return ByteBuffer::fromBase64Url($data ?? '')->getBinaryString();
	}

	/**
	 * Encodes a raw binary string as base64url
	 */
	public function encode(string $data): string
	{
		return (new ByteBuffer($data))->jsonSerialize();
	}

	/**
	 * Creates an instance scoped to a user,
	 * for registration and challenge-based login
	 */
	public static function for(
		User $user,
		array $formats = ['none']
	): self {
		$uid   = $user->id();
		$email = $user->email() ?? $uid;
		$name  = $user->name()->or($email);
		$kirby = $user->kirby();

		return new self(
			id:      static::rpId($kirby),
			title:   static::rpTitle($kirby),
			user:    $uid,
			email:   $email,
			name:    $name,
			formats: $formats
		);
	}

	/**
	 * Builds the public key options for a login,
	 * limited to the user's registered credentials
	 */
	public function loginOptions(array $credentials): array
	{
		$allow = [];

		foreach ($credentials as $credential) {
			if (is_string($credential['id'] ?? null) === true) {
				$allow[] = $this->bufferId($credential);
			}
		}

		return $this->options(
			parent::getGetArgs($allow, requireUserVerification: true)
		);
	}

	/**
	 * Normalizes the library's options object into an array
	 * for the Panel and injects the relying party id
	 */
	protected function options(object $options): array
	{
		$options->publicKey->rpId ??= $this->id;
		return json_decode(json_encode($options->publicKey), true);
	}

	/**
	 * Turns the client payload (JSON string, object or array)
	 * into an array
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function payload(mixed $payload): array
	{
		// allow JSON/string payloads from the client
		if (is_string($payload) === true) {
			$decoded = json_decode($payload, true);

			if (is_array($decoded) === true) {
				$payload = $decoded;
			}
		}

		if ($payload instanceof stdClass) {
			$payload = (array)$payload;
		}

		if (is_array($payload) === false) {
			throw new InvalidArgumentException(
				key: 'user.webauthn.invalid'
			);
		}

		return $payload;
	}

	/**
	 * Builds the public key options for registering a new passkey,
	 * excluding already registered credentials
	 */
	public function registerOptions(array $credentials): array
	{
		return $this->options(parent::getCreateArgs(
			userId:                  $this->user,
			userName:                $this->email,
			userDisplayName:         $this->name,
			requireResidentKey:      true,
			requireUserVerification: true,
			excludeCredentialIds:    A::map(
				$credentials,
				fn ($credential) => $this->bufferId($credential)
			)
		));
	}

	/**
	 * Removes a credential by id and returns the remaining list
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function removeCredential(array $credentials, string $id): array
	{
		$remaining = A::filter(
			$credentials,
			fn ($credential) => ($credential['id'] ?? null) !== $id
		);

		if (count($remaining) === count($credentials)) {
			throw new InvalidArgumentException(
				key: 'user.webauthn.notFound'
			);
		}

		return array_values($remaining);
	}

	/**
	 * Relying party id: the effective domain of the site
	 */
	protected static function rpId(App $kirby): string
	{
		$host = parse_url($kirby->site()->url(), PHP_URL_HOST);
		return is_string($host) === true ? $host : 'localhost';
	}

	/**
	 * Relying party name: the site title
	 */
	protected static function rpTitle(App $kirby): string
	{
		return $kirby->site()->title()->or(static::rpId($kirby));
	}

	/**
	 * Creates a site-wide instance for usernameless login,
	 * where no account is known yet
	 */
	public static function site(
		App $kirby,
		array $formats = ['none']
	): self {
		return new self(
			id:      static::rpId($kirby),
			title:   static::rpTitle($kirby),
			formats: $formats
		);
	}

	/**
	 * Verifies an assertion against a stored credential and
	 * returns the new signature counter. Every failure reports the
	 * same generic error so it cannot be used as an oracle.
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function verifyAssertion(
		array $payload,
		array $credential,
		string $challenge,
		bool $requireUserVerification = true,
		bool $requireUserPresent = true
	): int|null {
		$publicKey = $credential['publicKey'] ?? null;
		$counter   = $credential['counter'] ?? null;

		try {
			$clientDataJSON    = $this->decode($payload['client'] ?? null);
			$authenticatorData = $this->decode($payload['authenticator'] ?? null);
			$signature         = $this->decode($payload['signature'] ?? null);
			$rawId             = $this->decode($payload['rawId'] ?? null);

			if (
				is_string($publicKey) === false ||
				$rawId !== $this->decode($credential['id'] ?? '')
			) {
				throw new InvalidArgumentException(
					key: 'user.webauthn.failed'
				);
			}

			$this->processGet(
				$clientDataJSON,
				$authenticatorData,
				$signature,
				$publicKey,
				$this->decode($challenge),
				$counter,
				$requireUserVerification,
				$requireUserPresent
			);
		} catch (WebAuthnException $e) {
			// keep the reason for the server log, but never expose it
			throw new InvalidArgumentException(
				key:      'user.webauthn.failed',
				previous: $e
			);
		}

		return $this->getSignatureCounter();
	}

	/**
	 * Verifies a login and updates the matching credential's
	 * signature counter
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function verifyLogin(
		array $credentials,
		mixed $payload,
		string $challenge
	): array {
		$payload   = $this->payload($payload);
		$challenge = $this->challenge($challenge);
		$id        = $payload['id'] ?? null;

		if (is_string($id) === false) {
			throw new InvalidArgumentException(
				key: 'user.webauthn.failed'
			);
		}

		$credential = $this->credential($credentials, $id);
		$counter    = $this->verifyAssertion($payload, $credential, $challenge);

		if ($counter !== null) {
			foreach ($credentials as $index => $entry) {
				if (($entry['id'] ?? null) === $id) {
					$credentials[$index]['counter'] = $counter;
					break;
				}
			}
		}

		return [
			'credentials' => $credentials,
			'counter'     => $counter
		];
	}

	/**
	 * Verifies the registration of a new passkey and
	 * returns the credential to store
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function verifyRegister(mixed $payload, string $challenge): array
	{
		$payload   = $this->payload($payload);
		$challenge = $this->challenge($challenge);

		try {
			$client      = $this->decode($payload['client'] ?? null);
			$attestation = $this->decode($payload['attestation'] ?? null);
			$rawId       = $this->decode($payload['rawId'] ?? null);

			$data = $this->processCreate(
				$client,
				$attestation,
				$this->decode($challenge),
				true,
				true,
				false
			);
		} catch (WebAuthnException $e) {
			// keep the reason for the server log, but never expose
			// internal library details to the client
			throw new InvalidArgumentException(
				key:      'user.webauthn.failed',
				previous: $e
			);
		}

		return [
			'id'          => $this->encode($rawId),
			'publicKey'   => $data->credentialPublicKey,
			'counter'     => $data->signatureCounter ?? 0,
			'createdAt'   => time(),
			'attestation' => $data->attestationFormat ?? null
		];
	}
}
