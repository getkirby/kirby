<?php

namespace Kirby\Auth\Service;

use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn as BaseWebauthn;
use stdClass;

/**
 * Wrapper for WebAuthn library that keeps base64url handling
 * and common operations in one place
 */
class Webauthn extends BaseWebauthn
{
	public function __construct(
		protected string $id,
		string $title,
		protected string $user,
		protected string $email,
		protected string $name,
		array $formats = ['none']
	) {
		ByteBuffer::$useBase64UrlEncoding = true;
		parent::__construct($title, $id, $formats, true);
	}

	protected function byId(array $credentials, string $id): array
	{
		foreach ($credentials as $credential) {
			if (($credential['id'] ?? null) === $id) {
				return $credential;
			}
		}

		throw new InvalidArgumentException(
			message: 'Passkey could not be found'
		);
	}

	public function decode(string|null $data): string
	{
		return ByteBuffer::fromBase64Url($data ?? '')->getBinaryString();
	}

	public function encode(string $data): string
	{
		return (new ByteBuffer($data))->jsonSerialize();
	}

	public static function for(
		User $user,
		array $formats = ['none']
	): self {
		$uid   = $user->id();
		$email = $user->email() ?? $uid;
		$name  = $user->name()->or($email);
		$kirby = $user->kirby();
		$id    = parse_url($kirby->site()->url(), PHP_URL_HOST) ?? 'localhost';
		$title = $kirby->site()->title()->value();

		return new self($id, $title, $uid, $email, $name, $formats);
	}

	public function loginOptions(array $credentials): array
	{
		$allow = [];

		foreach ($credentials as $credential) {
			if (is_string($credential['id'] ?? null) === true) {
				$allow[] = ByteBuffer::fromBase64Url($credential['id']);
			}
		}

		return $this->publicKeyOptions($allow);
	}

	protected function normalizeChallenge(mixed $challenge): string
	{
		if (is_string($challenge) === false || $challenge === '') {
			throw new InvalidArgumentException(
				message: 'The passkey challenge is missing or expired'
			);
		}

		return $challenge;
	}

	protected function normalizePayload(mixed $code): array
	{
		// allow JSON/string payloads from the client
		if (is_string($code) === true) {
			$decoded = json_decode($code, true);
			if (is_array($decoded) === true) {
				$code = $decoded;
			}
		}

		if ($code instanceof stdClass) {
			$code = (array)$code;
		}

		if (is_array($code) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid passkey data'
			);
		}

		return $code;
	}

	public function publicKeyOptions(array $allow): array
	{
		$options = parent::getGetArgs($allow);
		$options->publicKey->rpId ??= $this->id;
		return json_decode(json_encode($options->publicKey), true);
	}

	public function removeCredential(array $credentials, string $id): array
	{
		$remaining = A::filter(
			$credentials,
			fn ($credential) => ($credential['id'] ?? null) !== $id
		);

		if (count($remaining) === count($credentials)) {
			throw new InvalidArgumentException(
				message: 'Passkey could not be found'
			);
		}

		return array_values($remaining);
	}

	public function registerOptions(array $credentials): array
	{
		$options = parent::getCreateArgs(
			$this->user,
			$this->email,
			$this->name
		);

		$options->publicKey->excludeCredentials = A::map(
			$credentials,
			fn ($credential) => ByteBuffer::fromBase64Url($credential['id'] ?? '')
		);

		$challenge = $this->getChallenge()->getBinaryString();
		$challenge = $this->encode($challenge);
		$publicKey = json_decode(json_encode($options->publicKey), true);
		$options->publicKey->rpId ??= $this->id;

		return $publicKey;
	}

	public function verifyGet(
		array $payload,
		array $credential,
		string $challenge,
		bool $requireUserVerification = true,
		bool $requireUserPresent = true
	): int|null {
		$clientDataJSON    = $this->decode($payload['clientDataJSON'] ?? null);
		$authenticatorData = $this->decode($payload['authenticatorData'] ?? null);
		$signature         = $this->decode($payload['signature'] ?? null);
		$rawId             = $this->decode($payload['rawId'] ?? null);
		$publicKey         = $credential['publicKey'] ?? null;
		$counter           = $credential['counter'] ?? null;

		if (is_string($publicKey) === false) {
			throw new InvalidArgumentException(
				message: 'Passkey is incomplete'
			);
		}

		if ($rawId !== $this->decode($credential['id'] ?? '')) {
			throw new InvalidArgumentException(
				message: 'Passkey id does not match'
			);
		}

		$challenge = $this->decode($challenge);

		$this->processGet(
			$clientDataJSON,
			$authenticatorData,
			$signature,
			$publicKey,
			$challenge,
			$counter,
			$requireUserVerification,
			$requireUserPresent
		);

		return $this->getSignatureCounter();
	}

	public function verifyLogin(
		array $credentials,
		mixed $payload,
		string $challenge
	): array {
		$payload   = $this->normalizePayload($payload);
		$challenge = $this->normalizeChallenge($challenge);
		$id        = $payload['id'] ?? null;

		if (is_string($id) === false) {
			throw new InvalidArgumentException(
				message: 'Passkey id is missing'
			);
		}

		$credential = $this->byId($credentials, $id);
		$newCounter = $this->verifyGet(
			[
				'clientDataJSON'    => $payload['clientDataJSON'] ?? null,
				'authenticatorData' => $payload['authenticatorData'] ?? null,
				'signature'         => $payload['signature'] ?? null,
				'rawId'             => $payload['rawId'] ?? null
			],
			$credential,
			$challenge
		);

		if ($newCounter !== null) {
			foreach ($credentials as &$entry) {
				if (($entry['id'] ?? null) === $id) {
					$entry['counter'] = $newCounter;
					break;
				}
			}
		}

		return [
			'credentials' => $credentials,
			'counter'     => $newCounter
		];
	}

	public function verifyRegister(mixed $payload, string $challenge): array
	{
		$payload     = $this->normalizePayload($payload);
		$challenge   = $this->normalizeChallenge($challenge);
		$client      = $this->decode($payload['clientDataJSON'] ?? null);
		$attestation = $this->decode($payload['attestationObject'] ?? null);
		$rawId       = $this->decode($payload['rawId'] ?? null);

		$data = $this->processCreate(
			$client,
			$attestation,
			$this->decode($challenge),
			true,
			true,
			false
		);

		return [
			'id'          => $this->encode($rawId),
			'publicKey'   => $data->credentialPublicKey,
			'counter'     => $data->signatureCounter ?? 0,
			'createdAt'   => time(),
			'attestation' => $data->attestationFormat ?? null
		];
	}
}
