<?php

namespace Kirby\Auth\Service;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Webauthn::class)]
class WebauthnTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Webauthn';

	protected function setUp(): void
	{
		parent::setUp();
		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		Dir::remove(static::TMP);
	}

	protected function webauthn(): Webauthn
	{
		return new Webauthn(
			id:    'example.com',
			title: 'Example',
			user:  'user-123',
			email: 'user@example.com',
			name:  'User Name'
		);
	}

	protected function app(array $props = []): App
	{
		return new App([
			'roots' => ['index' => static::TMP],
			'urls'  => ['index' => 'https://example.com'],
			'site'  => ['content' => ['title' => 'Example Site']],
			...$props
		]);
	}

	/**
	 * Creates a login assertion for the given instance, exactly as
	 * a browser would post it for a get()
	 *
	 * @return array{id: string, publicKey: string, payload: array, challenge: string}
	 */
	public static function assertion(
		Webauthn $webauthn,
		int $signCount = 0,
		string $host = 'example.com'
	): array {
		// generate an ES256 (P-256) credential keypair
		$key = openssl_pkey_new([
			'private_key_type' => OPENSSL_KEYTYPE_EC,
			'curve_name'       => 'prime256v1'
		]);
		$pem = openssl_pkey_get_details($key)['key'];

		// same base64url string is stored as the challenge and echoed
		// back inside the client data
		$challenge    = $webauthn->encode(str_repeat("\x2a", 32));
		$credentialId = $webauthn->encode("\x01\x02\x03\x04");

		// client data as the browser would produce it for a get()
		$clientDataJSON = json_encode([
			'type'      => 'webauthn.get',
			'challenge' => $challenge,
			'origin'    => 'https://' . $host
		]);

		// authenticator data: rpIdHash + flags (user present + verified)
		// + big-endian signature counter
		$authenticatorData =
			hash('sha256', $host, true) .
			chr(0x05) .
			pack('N', $signCount);

		// sign authenticatorData . sha256(clientDataJSON) with the key
		openssl_sign(
			$authenticatorData . hash('sha256', $clientDataJSON, true),
			$signature,
			$key,
			OPENSSL_ALGO_SHA256
		);

		return [
			'id'        => $credentialId,
			'publicKey' => $pem,
			'challenge' => $challenge,
			'payload'   => [
				'id'            => $credentialId,
				'rawId'         => $credentialId,
				'client'        => $webauthn->encode($clientDataJSON),
				'authenticator' => $webauthn->encode($authenticatorData),
				'signature'     => $webauthn->encode($signature)
			]
		];
	}

	/**
	 * Creates valid 'none'-format registration attestation for the
	 * given instance, the way a browser/authenticator would post it.
	 *
	 * @return array{payload: array, challenge: string}
	 */
	public static function attestation(
		Webauthn $webauthn,
		string $host = 'example.com'
	): array {
		// real P-256 keypair → raw x / y for the COSE key
		$key = openssl_pkey_new([
			'private_key_type' => OPENSSL_KEYTYPE_EC,
			'curve_name'       => 'prime256v1'
		]);
		$ec = openssl_pkey_get_details($key)['ec'];

		// COSE_Key: {1:2 (EC2), 3:-7 (ES256), -1:1 (P-256), -2:x, -3:y}
		$cose =
			chr((5 << 5) | 5) .
			static::cbor(0, 1) . static::cbor(0, 2) .
			static::cbor(0, 3) . static::cbor(1, 6) .
			static::cbor(1, 0) . static::cbor(0, 1) .
			static::cbor(1, 1) . static::cbor(2, $ec['x']) .
			static::cbor(1, 2) . static::cbor(2, $ec['y']);

		$credentialId = "\x01\x02\x03\x04";

		// authenticatorData: rpIdHash + flags (user present + verified +
		// attested data) + signCount + aaguid + credential id + COSE key
		$authData =
			hash('sha256', $host, true) .
			chr(0x45) .
			pack('N', 0) .
			str_repeat("\x00", 16) .
			pack('n', strlen($credentialId)) .
			$credentialId .
			$cose;

		// attestationObject: {fmt:'none', attStmt:{}, authData:<bytes>}
		$attestation =
			chr((5 << 5) | 3) .
			static::cbor(3, 'fmt')      . static::cbor(3, 'none') .
			static::cbor(3, 'attStmt')  . chr((5 << 5) | 0) .
			static::cbor(3, 'authData') . static::cbor(2, $authData);

		$challenge = $webauthn->encode(str_repeat("\x2a", 32));

		$clientDataJSON = json_encode([
			'type'      => 'webauthn.create',
			'challenge' => $challenge,
			'origin'    => 'https://' . $host
		]);

		return [
			'payload' => [
				'rawId'       => $webauthn->encode($credentialId),
				'client'      => $webauthn->encode($clientDataJSON),
				'attestation' => $webauthn->encode($attestation)
			],
			'challenge' => $challenge
		];
	}

	protected static function cbor(int $major, int|string $value): string
	{
		if (is_int($value) === true) {
			return $value < 24 ? chr(($major << 5) | $value) : chr(($major << 5) | 24) . chr($value);
		}

		$len  = strlen($value);
		$head = $len < 24 ? chr(($major << 5) | $len) : chr(($major << 5) | 24) . chr($len);
		return $head . $value;
	}

	/**
	 * Wraps assertion() with the matching stored credential, for the
	 * service tests that verify a full login end to end
	 *
	 * @return array{0: Webauthn, 1: array, 2: array, 3: string}
	 */
	protected function validAssertion(int $signCount = 5): array
	{
		$webauthn = $this->webauthn();
		$assert   = static::assertion($webauthn, $signCount);

		$credentials = [[
			'id'        => $assert['id'],
			'publicKey' => $assert['publicKey'],
			'counter'   => 0
		]];

		return [$webauthn, $credentials, $assert['payload'], $assert['challenge']];
	}

	public function testEncodeDecode(): void
	{
		$webauthn = $this->webauthn();

		// known base64url vector for the bytes 1, 2, 3
		$this->assertSame('AQID', $webauthn->encode("\x01\x02\x03"));
		$this->assertSame("\x01\x02\x03", $webauthn->decode('AQID'));

		// url-safe alphabet: 0xFF encodes to "_w" (not "/w")
		$this->assertSame('_w', $webauthn->encode("\xff"));
		$this->assertSame("\xff", $webauthn->decode('_w'));
	}

	public function testDecodeHandlesEmptyInput(): void
	{
		$this->assertSame('', $this->webauthn()->decode(null));
		$this->assertSame('', $this->webauthn()->decode(''));
	}

	public function testLoginOptions(): void
	{
		$options = $this->webauthn()->loginOptions([
			['id' => 'AQID'],
			['id' => 'AgME'],
			['name' => 'no id here'] // no string id → skipped
		]);

		$this->assertSame('example.com', $options['rpId']);
		$this->assertSame('required', $options['userVerification']);
		$this->assertIsString($options['challenge']);
		$this->assertNotSame('', $options['challenge']);

		$this->assertCount(2, $options['allowCredentials']);
		$this->assertSame('AQID', $options['allowCredentials'][0]['id']);
		$this->assertSame('AgME', $options['allowCredentials'][1]['id']);
		$this->assertSame('public-key', $options['allowCredentials'][0]['type']);
	}

	public function testLoginOptionsWithoutCredentials(): void
	{
		$options = $this->webauthn()->loginOptions([]);

		$this->assertSame('required', $options['userVerification']);
		$this->assertArrayNotHasKey('allowCredentials', $options);
	}

	public function testRegisterOptions(): void
	{
		$webauthn = $this->webauthn();
		$options  = $webauthn->registerOptions([
			['id' => 'AQID']
		]);

		$this->assertSame('example.com', $options['rpId']);

		// user verification + resident key are requested,
		// not just enforced
		$selection = $options['authenticatorSelection'];
		$this->assertSame('required', $selection['userVerification']);
		$this->assertSame('required', $selection['residentKey']);
		$this->assertTrue($selection['requireResidentKey']);

		// already registered credentials are excluded as proper
		// { id, type } descriptors (not bare id strings)
		$this->assertCount(1, $options['excludeCredentials']);
		$this->assertSame('AQID', $options['excludeCredentials'][0]['id']);
		$this->assertSame('public-key', $options['excludeCredentials'][0]['type']);

		// user identity is carried over
		$this->assertSame('user@example.com', $options['user']['name']);
		$this->assertSame('User Name', $options['user']['displayName']);
		$this->assertSame('user-123', $webauthn->decode($options['user']['id']));

		$this->assertNotEmpty($options['pubKeyCredParams']);
		$this->assertIsString($options['challenge']);
	}

	public function testRemoveCredential(): void
	{
		$credentials = [
			['id' => 'aaa', 'name' => 'Key 1'],
			['id' => 'bbb', 'name' => 'Key 2']
		];

		$remaining = $this->webauthn()->removeCredential($credentials, 'aaa');

		$this->assertCount(1, $remaining);
		$this->assertSame('bbb', $remaining[0]['id']);
	}

	public function testRemoveCredentialToEmpty(): void
	{
		$remaining = $this->webauthn()->removeCredential([['id' => 'aaa']], 'aaa');
		$this->assertSame([], $remaining);
	}

	public function testRemoveCredentialNotFound(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Passkey could not be found');

		$this->webauthn()->removeCredential([['id' => 'aaa']], 'zzz');
	}

	public function testVerifyLoginMissingId(): void
	{
		// login failures all report the same generic message so they
		// cannot be used to distinguish why verification failed
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey could not be verified');

		$this->webauthn()->verifyLogin([], ['client' => 'x'], 'challenge');
	}

	public function testVerifyLoginUnknownCredential(): void
	{
		// same generic message as a missing id (no credential-existence oracle)
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey could not be verified');

		$this->webauthn()->verifyLogin([['id' => 'aaa']], ['id' => 'zzz'], 'challenge');
	}

	public function testVerifyLoginUniformsLibraryFailure(): void
	{
		// a well-formed request that fails inside the WebAuthn library
		// (here: empty authenticator data) is reported with the same
		// generic error, never the library's specific reason
		$credentials = [[
			'id'        => 'AQID',
			'publicKey' => 'dummy',
			'counter'   => 0
		]];
		$payload = [
			'id'     => 'AQID',
			'rawId'  => 'AQID',
			'client' => ''
		];

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey could not be verified');

		$this->webauthn()->verifyLogin($credentials, $payload, 'AQID');
	}

	public function testVerifyLoginMissingChallenge(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey challenge is missing or expired');

		$this->webauthn()->verifyLogin([], ['id' => 'aaa'], '');
	}

	public function testVerifyLoginInvalidPayload(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid passkey data');

		$this->webauthn()->verifyLogin([], 123, 'challenge');
	}

	public function testVerifyLoginAcceptsJsonStringPayload(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey could not be verified');

		$this->webauthn()->verifyLogin([], '{"client":"x"}', 'challenge');
	}

	public function testVerifyLoginAcceptsObjectPayload(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey could not be verified');

		$this->webauthn()->verifyLogin([], (object)['id' => 'zzz'], 'challenge');
	}

	public function testVerifyLoginRejectsNonStringPublicKey(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey could not be verified');

		$credentials = [['id' => 'AQID']]; // no publicKey
		$payload     = ['id' => 'AQID', 'rawId' => 'AQID'];

		$this->webauthn()->verifyLogin($credentials, $payload, 'AQID');
	}

	public function testVerifyLoginRejectsRawIdMismatch(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey could not be verified');

		$credentials = [['id' => 'AQID', 'publicKey' => 'dummy']];
		$payload     = ['id' => 'AQID', 'rawId' => 'AgME']; // decodes differently

		$this->webauthn()->verifyLogin($credentials, $payload, 'AQID');
	}

	public function testVerifyLoginSucceedsAndUpdatesCounter(): void
	{
		[$webauthn, $credentials, $payload, $challenge] = $this->validAssertion(5);

		$result = $webauthn->verifyLogin($credentials, $payload, $challenge);

		// the authenticator's new counter is returned and written back
		// onto the matching stored credential
		$this->assertSame(5, $result['counter']);
		$this->assertSame(5, $result['credentials'][0]['counter']);
	}

	public function testVerifyRegisterMissingChallenge(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey challenge is missing or expired');

		$this->webauthn()->verifyRegister(['client' => 'x'], '');
	}

	public function testVerifyRegisterInvalidPayload(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid passkey data');

		$this->webauthn()->verifyRegister(3.14, 'challenge');
	}

	public function testVerifyRegisterUniformsLibraryFailure(): void
	{
		// a well-formed payload that fails inside the WebAuthn library
		// (here: empty attestation) is reported with the same generic
		// error instead of leaking the library's internal exception
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey could not be verified');

		$this->webauthn()->verifyRegister(
			['client' => '', 'attestation' => '', 'rawId' => 'AQID'],
			'AQID'
		);
	}

	public function testVerifyRegisterSucceeds(): void
	{
		$webauthn = $this->webauthn();
		['payload' => $payload, 'challenge' => $challenge] = static::attestation($webauthn);

		$credential = $webauthn->verifyRegister($payload, $challenge);

		// the stored id echoes the payload's rawId (base64url of the bytes)
		$this->assertSame('AQIDBA', $credential['id']);

		// the public key is derived from the attested COSE key
		$this->assertStringStartsWith(
			'-----BEGIN PUBLIC KEY-----',
			$credential['publicKey']
		);

		// a zero sign counter falls back to 0, the format is carried over
		// and a creation timestamp is stamped
		$this->assertSame(0, $credential['counter']);
		$this->assertSame('none', $credential['attestation']);
		$this->assertIsInt($credential['createdAt']);
	}

	public function testForUser(): void
	{
		$app  = $this->app([
			'users' => [
				['id' => 'abc', 'email' => 'jane@example.com', 'name' => 'Jane']
			]
		]);
		$user = $app->user('jane@example.com');

		$options = Webauthn::for($user)->registerOptions([]);

		// relying party derives from the site
		$this->assertSame('example.com', $options['rpId']);
		$this->assertSame('example.com', $options['rp']['id']);
		$this->assertSame('Example Site', $options['rp']['name']);

		// user identity derives from the account
		$this->assertSame('jane@example.com', $options['user']['name']);
		$this->assertSame('Jane', $options['user']['displayName']);
	}

	public function testSite(): void
	{
		$options = Webauthn::site($this->app())->loginOptions([]);

		$this->assertSame('example.com', $options['rpId']);
		$this->assertSame('required', $options['userVerification']);
		$this->assertIsString($options['challenge']);
	}
}
