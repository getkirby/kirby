<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Cms\User;
use Kirby\Data\Json;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Ui\Dialog;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;

class UserWebauthnDialogController extends UserDialogController
{
	protected WebAuthn $webauthn;
	protected User $currentUser;

	public function __construct(User $user)
	{
		parent::__construct($user);

		ByteBuffer::$useBase64UrlEncoding = true;
		$this->currentUser                 = $this->kirby->user();
		$this->webauthn                    = new WebAuthn(
			$this->kirby->site()->title()->value(),
			$this->rpId(),
			['none']
		);
	}

	public static function factory(string|null $id = null): static
	{
		return new static($id ? Find::user($id) : App::instance()->user());
	}

	public function load(): Dialog
	{
		$this->ensurePermission();

		return new Dialog(
			component: 'k-webauthn-dialog',
			size: 'large',
			submitButton: false,
			credentials: $this->credentials(),
			options: $this->creationOptions()
		);
	}

	public function submit(): array
	{
		$this->ensurePermission();

		return match ($this->request->get('action')) {
			'create' => $this->register(),
			'remove' => $this->remove(),
			default    => throw new InvalidArgumentException(
				key: 'webauthn.action.invalid',
				fallback: 'Invalid WebAuthn action'
			)
		};
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

	protected function creationOptions(): array
	{
		$options = $this->webauthn->getCreateArgs(
			$this->user->id(),
			$this->user->email() ?? $this->user->id(),
			$this->user->name()->or($this->user->email() ?? $this->user->id())
		);

		// prevent re-registering existing credentials
		$exclude = [];
		foreach ($this->storedCredentials() as $credential) {
			if ($id = $credential['id'] ?? null) {
				$exclude[] = ByteBuffer::fromBase64Url($id);
			}
		}

		$options->publicKey->excludeCredentials = $exclude;

		// persist the challenge to validate the response later
		$this->kirby->session()->set(
			$this->sessionKey(),
			$this->base64UrlEncode($this->webauthn->getChallenge()->getBinaryString())
		);

		// Convert to array to satisfy strict return types and JSON encoding
		return Json::decode(Json::encode($options), true);
	}

	protected function credentials(): array
	{
		return array_map(
			function ($credential) {
				return [
					'id'        => $credential['id'],
					'name'      => $credential['name'] ?? $credential['id'],
					'createdAt' => $credential['createdAt'] ?? null,
					'isBackup'  => $credential['isBackedUp'] ?? null
				];
			},
			$this->storedCredentials()
		);
	}

	protected function ensurePermission(): void
	{
		if ($this->currentUser->is($this->user) === true) {
			return;
		}

		if ($this->currentUser->isAdmin() === true) {
			return;
		}

		throw new PermissionException(
			message: 'You are not allowed to manage passkeys for this user'
		);
	}

	protected function register(): array
	{
		$payload    = $this->request->get();
		$credential = $payload['credential'] ?? null;

		if (is_array($credential) === false) {
			throw new InvalidArgumentException(
				key: 'webauthn.payload.invalid',
				fallback: 'Missing passkey data'
			);
		}

		$challenge = $this->kirby->session()->pull($this->sessionKey());

		if ($challenge === null) {
			throw new InvalidArgumentException(
				key: 'webauthn.challenge.missing',
				fallback: 'The passkey challenge is missing or expired'
			);
		}

		$clientDataJSON     = $this->base64UrlDecode($credential['clientDataJSON'] ?? null);
		$attestationObject  = $this->base64UrlDecode($credential['attestationObject'] ?? null);
		$rawId              = $this->base64UrlDecode($credential['rawId'] ?? null);
		$credentialName     = trim($payload['name'] ?? '') ?: 'Passkey ' . (count($this->storedCredentials()) + 1);
		$binaryChallenge    = $this->base64UrlDecode($challenge);

		try {
			$data = $this->webauthn->processCreate(
				$clientDataJSON,
				$attestationObject,
				$binaryChallenge,
				true,
				true,
				false
			);
		} catch (WebAuthnException $e) {
			throw new InvalidArgumentException(
				key: 'webauthn.register.failed',
				fallback: $e->getMessage(),
				previous: $e
			);
		}

		$credentials   = $this->storedCredentials();
		$credentials[] = [
			'id'          => $this->base64UrlEncode($rawId),
			'name'        => $credentialName,
			'publicKey'   => $data->credentialPublicKey,
			'counter'     => $data->signatureCounter ?? 0,
			'createdAt'   => time(),
			'attestation' => $data->attestationFormat ?? null,
			'isBackedUp'  => $data->isBackedUp ?? null
		];

		$this->user->changeSecret('webauthn', $credentials);

		return [
			'message' => 'Passkey created'
		];
	}

	protected function remove(): array
	{
		$id = $this->request->get('id');

		if (is_string($id) === false || $id === '') {
			throw new InvalidArgumentException(
				message: 'Passkey id is missing'
			);
		}

		$remaining = array_values(
			array_filter(
				$this->storedCredentials(),
				fn ($credential) => ($credential['id'] ?? null) !== $id
			)
		);

		if (count($remaining) === count($this->storedCredentials())) {
			throw new NotFoundException(
				message: 'Passkey could not be found'
			);
		}

		$this->user->changeSecret('webauthn', $remaining === [] ? null : $remaining);

		return [
			'message' => 'Passkey removed'
		];
	}

	protected function rpId(): string
	{
		return parse_url($this->kirby->site()->url(), PHP_URL_HOST) ?? 'localhost';
	}

	protected function sessionKey(): string
	{
		return 'webauthn.challenge.' . $this->user->id();
	}

	protected function storedCredentials(): array
	{
		$credentials = $this->user->secret('webauthn');

		return $credentials === null || is_array($credentials) === false
			? []
			: array_values($credentials);
	}
}
