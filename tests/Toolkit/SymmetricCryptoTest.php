<?php

namespace Kirby\Toolkit;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SymmetricCrypto::class)]
class SymmetricCryptoTest extends TestCase
{
	public function setUp(): void
	{
		if (defined('SODIUM_LIBRARY_VERSION') !== true) {
			$this->markTestSkipped('PHP sodium extension is not available');
		}
	}

	public function testConstructKeyAndPassword()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Passing both a secret key and a password is not supported');

		new SymmetricCrypto(secretKey: 'abcdefghijklmnopabcdefghijklmnop', password: 'super secure');
	}

	public function testConstructKeyLength()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid secret key length');

		new SymmetricCrypto(secretKey: 'not secure');
	}

	public function testDebugInfo()
	{
		$crypto = new SymmetricCrypto();
		$this->assertSame([
			'hasPassword'  => false,
			'hasSecretKey' => false,
		], $crypto->__debugInfo());
		$crypto->secretKey();
		$this->assertSame([
			'hasPassword'  => false,
			'hasSecretKey' => true,
		], $crypto->__debugInfo());

		$crypto = new SymmetricCrypto(secretKey: 'abcdefghijklmnopabcdefghijklmnop');
		$this->assertSame([
			'hasPassword'  => false,
			'hasSecretKey' => true,
		], $crypto->__debugInfo());

		$crypto = new SymmetricCrypto(password: 'super secure');
		$this->assertSame([
			'hasPassword'  => true,
			'hasSecretKey' => false,
		], $crypto->__debugInfo());
	}

	public function testDestruct()
	{
		// helper to access protected props by reference
		$reader = fn () => [
			'password'            => &$this->password,
			'secretKey'           => &$this->secretKey,
			'secretKeysByOptions' => &$this->secretKeysByOptions,
		];

		$crypto = new SymmetricCrypto(secretKey: $secretKey = 'abcdefghijklmnopabcdefghijklmnop');
		$values = Closure::bind($reader, $crypto, $crypto)();
		$this->assertSame(['password' => null, 'secretKey' => $secretKey, 'secretKeysByOptions' => []], $values);
		unset($crypto);
		$this->assertSame(['password' => null, 'secretKey' => '', 'secretKeysByOptions' => []], $values);

		$crypto = new SymmetricCrypto(password: $password = 'super secure');
		$crypto->secretKey(str_repeat('A', SODIUM_CRYPTO_PWHASH_SALTBYTES), [SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE]);
		$crypto->secretKey(str_repeat('B', SODIUM_CRYPTO_PWHASH_SALTBYTES), [SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE]);
		$values = Closure::bind($reader, $crypto, $crypto)();
		$limits = SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE . ',' . SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE;
		$this->assertSame([
			'password' => $password,
			'secretKey' => null,
			'secretKeysByOptions' => [
				str_repeat('A', SODIUM_CRYPTO_PWHASH_SALTBYTES) . ':' . $limits => hex2bin('8bfb935f72ecc1c77aecde1a44168f73aed70a21d35d8bd11c8c43dcec07ccb3'),
				str_repeat('B', SODIUM_CRYPTO_PWHASH_SALTBYTES) . ':' . $limits => hex2bin('957a8b8b435aefb479351c8c5b5dcf0ca76253846d47c69dcc0ddba569c14d62'),
			]
		], $values);
		unset($crypto);
		$this->assertSame(['password' => '', 'secretKey' => null, 'secretKeysByOptions' => []], $values);
	}

	public function testDecrypt()
	{
		$crypto = new SymmetricCrypto(secretKey: 'abcdefghijklmnopabcdefghijklmnop');

		$input = '{"mode":"secretbox","data":"tXH/eNjDMhL+BIgPDWkeYZvBpqBbMKEfUhoHHrhLde7gbTBGqsz1IzhG7Q==",' .
			'"nonce":"X3xgVLA1Zyffp5/AOlZQeccD6aynPj43","salt":"VHC6wQ2g7Z9+HK0XJUWV6Q==","limits":[2,67108864]}';

		$this->assertSame('a very confidential message', $crypto->decrypt($input));
	}

	public function testDecryptInvalidJson()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('JSON string is invalid');

		$crypto = new SymmetricCrypto(secretKey: 'abcdefghijklmnopabcdefghijklmnop');
		$crypto->decrypt('not JSON!');
	}

	public function testDecryptInvalidMode()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Unsupported encryption mode "box"');

		$crypto = new SymmetricCrypto(secretKey: 'abcdefghijklmnopabcdefghijklmnop');

		$input = '{"mode":"box","data":"L9kUIhjbXC/gMkiJtt/HHmhJVMJ7nFek8t0GUy9AkWwZjLJpKJxHEIZ/vA==",' .
			'"nonce":"QZ9NM4JIP0jDgfzQRe1ir6fcAwjBuRKZ"}';

		$crypto->decrypt($input);
	}

	public function testDecryptMissingProps()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Input data does not contain all required props');

		$crypto = new SymmetricCrypto(secretKey: 'abcdefghijklmnopabcdefghijklmnop');
		$crypto->decrypt('{"mode":"secretbox","data":"this is set","nonce":"this is also set"}');
	}

	public function testDecryptTampered1()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Encrypted string was tampered with');

		$crypto = new SymmetricCrypto(password: 'super secure');

		// modified data
		$input = '{"mode":"secretbox","data":"tXH/eNjDMiL+BIgPDWkeYZvBpqBbMKEfUhoHHrhLde7gbTBGqsz1IzhG7Q==",' .
			'"nonce":"X3xgVLA1Zyffp5/AOlZQeccD6aynPj43","salt":"VHC6wQ2g7Z9+HK0XJUWV6Q==","limits":[2,67108864]}';

		$crypto->decrypt($input);
	}

	public function testDecryptTampered2()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Encrypted string was tampered with');

		$crypto = new SymmetricCrypto(password: 'super secure');

		// modified nonce
		$input = '{"mode":"secretbox","data":"tXH/eNjDMhL+BIgPDWkeYZvBpqBbMKEfUhoHHrhLde7gbTBGqsz1IzhG7Q==",' .
			'"nonce":"X3xgVLA1Zzffp5/AOlZQeccD6aynPj43","salt":"VHC6wQ2g7Z9+HK0XJUWV6Q==","limits":[2,67108864]}';

		$crypto->decrypt($input);
	}

	public function testDecryptTampered3()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Encrypted string was tampered with');

		$crypto = new SymmetricCrypto(password: 'super secure');

		// modified salt
		$input = '{"mode":"secretbox","data":"tXH/eNjDMhL+BIgPDWkeYZvBpqBbMKEfUhoHHrhLde7gbTBGqsz1IzhG7Q==",' .
			'"nonce":"X3xgVLA1Zyffp5/AOlZQeccD6aynPj43","salt":"VHC6wQ2g7Z9+HK0YJUWV6Q==","limits":[2,67108864]}';

		$crypto->decrypt($input);
	}

	public function testDecryptTampered4()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Encrypted string was tampered with');

		$crypto = new SymmetricCrypto(password: 'super secure');

		// modified limits
		$input = '{"mode":"secretbox","data":"tXH/eNjDMhL+BIgPDWkeYZvBpqBbMKEfUhoHHrhLde7gbTBGqsz1IzhG7Q==",' .
			'"nonce":"X3xgVLA1Zyffp5/AOlZQeccD6aynPj43","salt":"VHC6wQ2g7Z9+HK0XJUWV6Q==","limits":[2,67208864]}';

		$crypto->decrypt($input);
	}

	public function testEncrypt()
	{
		$crypto1    = new SymmetricCrypto(secretKey: 'abcdefghijklmnopabcdefghijklmnop');
		$encrypted1 = $crypto1->encrypt($message = 'a very confidential message');

		$props1 = json_decode($encrypted1, true);
		$this->assertIsArray($props1);
		$this->assertSame(['mode', 'data', 'nonce', 'salt', 'limits'], array_keys($props1));
		$this->assertSame('secretbox', $props1['mode']);
		$this->assertSame(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, strlen(base64_decode($props1['nonce'])));
		$this->assertSame(SODIUM_CRYPTO_PWHASH_SALTBYTES, strlen(base64_decode($props1['salt'])));
		$this->assertSame(
			[SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE],
			$props1['limits']
		);

		// if we can decrypt the string again, encryption was probably successful
		$crypto2 = new SymmetricCrypto(secretKey: 'abcdefghijklmnopabcdefghijklmnop');
		$this->assertSame($message, $crypto1->decrypt($encrypted1));

		// encrypting again should use a different nonce and salt
		$encrypted2 = $crypto1->encrypt('a very confidential message');
		$this->assertNotSame($encrypted1, $encrypted2);
		$props2 = json_decode($encrypted2, true);
		$this->assertNotSame($props1['data'], $props2['data']);
		$this->assertNotSame($props1['nonce'], $props2['nonce']);
		$this->assertNotSame($props1['salt'], $props2['salt']);
	}

	public function testIsAvailable()
	{
		$this->assertTrue(SymmetricCrypto::isAvailable());
	}

	public function testSecretKeyFromKey()
	{
		$crypto = new SymmetricCrypto(secretKey: $key = 'abcdefghijklmnopabcdefghijklmnop');
		$this->assertSame($key, $crypto->secretKey());
	}

	public function testSecretKeyFromPassword()
	{
		$crypto = new SymmetricCrypto(password: 'super secure');
		$this->assertSame(
			'b3689bcad735e4537be1ad05185f482fab2d6242b4d30decf71b8c778b7fbf0d',
			bin2hex($crypto->secretKey('abcdefghijklmnop', [SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE]))
		);

		// cached result is the same
		$this->assertSame(
			'b3689bcad735e4537be1ad05185f482fab2d6242b4d30decf71b8c778b7fbf0d',
			bin2hex($crypto->secretKey('abcdefghijklmnop', [SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE]))
		);
	}

	public function testSecretKeyFromPasswordNoSalt()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Salt and limits are required when deriving a secret key from a password');

		$crypto = new SymmetricCrypto(password: 'super secure');
		$crypto->secretKey();
	}

	public function testSecretKeyRandom()
	{
		$crypto1 = new SymmetricCrypto();
		$key1   = $crypto1->secretKey();
		$key2   = $crypto1->secretKey();

		$crypto2 = new SymmetricCrypto();
		$key3   = $crypto2->secretKey();
		$key4   = $crypto2->secretKey();

		$this->assertSame(SODIUM_CRYPTO_SECRETBOX_KEYBYTES, strlen($key1));
		$this->assertSame(SODIUM_CRYPTO_SECRETBOX_KEYBYTES, strlen($key2));
		$this->assertSame(SODIUM_CRYPTO_SECRETBOX_KEYBYTES, strlen($key3));
		$this->assertSame(SODIUM_CRYPTO_SECRETBOX_KEYBYTES, strlen($key4));

		// each key is random
		$this->assertNotSame($key1, $key3);

		// key is cached
		$this->assertSame($key1, $key2);
		$this->assertSame($key3, $key4);
	}
}
