<?php

namespace Kirby\Session;

use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use Kirby\Toolkit\SymmetricCrypto;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Token::class)]
class TokenTest extends TestCase
{
	protected TestSessionStore $store;

	protected function setUp(): void
	{
		$this->store = new TestSessionStore();
	}

	protected function tearDown(): void
	{
		unset($this->store);
	}

	public function testConstruct(): void
	{
		$token = new Token(1234567890, 'someId', 'someKey');
		$this->assertSame(1234567890, $token->expiry);
		$this->assertSame('someId', $token->id);
		$this->assertSame('someKey', $token->key);

		// the key is optional
		$token = new Token(1234567890, 'someId');
		$this->assertNull($token->key);
	}

	public function testCrypto(): void
	{
		if (SymmetricCrypto::isAvailable() === false) {
			$this->markTestSkipped('The PHP `sodium` extension is not available');
		}

		$token  = new Token(1234567890, 'someId', $this->store->validKey);
		$crypto = $token->crypto();

		$this->assertInstanceOf(SymmetricCrypto::class, $crypto);

		// the crypto instance is based on the token key
		$message = 'a secret message';
		$this->assertSame($message, $crypto->decrypt($crypto->encrypt($message)));
	}

	public function testCryptoWithoutKey(): void
	{
		$token = new Token(1234567890, 'someId');

		// a readonly token has no key to build a crypto instance from
		$this->assertNull($token->crypto());
	}

	public function testGenerate(): void
	{
		$token = Token::generate($this->store, 9999999999);

		$this->assertSame(9999999999, $token->expiry);

		// the ID is reserved in the store
		$this->assertTrue($this->store->exists(9999999999, $token->id));

		// the ID is a random 20 character hex string
		$this->assertStringMatchesFormat('%x', $token->id);
		$this->assertSame(20, strlen($token->id));

		// the key is a random 64 character hex string
		$this->assertIsString($token->key);
		$this->assertStringMatchesFormat('%x', $token->key);
		$this->assertSame(64, strlen($token->key));

		// two generated tokens never collide
		$this->assertNotSame($token->id, Token::generate($this->store, 9999999999)->id);
	}

	public function testIsReadonly(): void
	{
		$this->assertFalse((new Token(1234567890, 'someId', 'someKey'))->isReadonly());
		$this->assertTrue((new Token(1234567890, 'someId'))->isReadonly());
	}

	public function testParse(): void
	{
		$token = Token::parse('1234567890.thisIsMyAwesomeId.' . $this->store->validKey);

		$this->assertSame(1234567890, $token->expiry);
		$this->assertSame('thisIsMyAwesomeId', $token->id);
		$this->assertSame($this->store->validKey, $token->key);
		$this->assertFalse($token->isReadonly());
		$this->assertSame('1234567890.thisIsMyAwesomeId.' . $this->store->validKey, $token->toString());
	}

	public function testParseWithoutKey(): void
	{
		$token = Token::parse('1234567890.thisIsMyAwesomeId', key: false);

		$this->assertSame(1234567890, $token->expiry);
		$this->assertSame('thisIsMyAwesomeId', $token->id);
		$this->assertNull($token->key);
		$this->assertTrue($token->isReadonly());
		$this->assertSame('1234567890.thisIsMyAwesomeId', $token->toString());
	}

	public function testParseTooFewParts(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid argument "$token" in method "Token::parse"');

		Token::parse('9999999999.thisIsNotAValidToken');
	}

	public function testParseTooManyParts(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid argument "$token" in method "Token::parse"');

		Token::parse('1234567890.thisIsMyAwesomeId.' . $this->store->validKey, key: false);
	}

	public function testParseReassemblyMismatch(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid argument "$token" in method "Token::parse"');

		// the expiry part is not a clean integer, so reassembly fails
		Token::parse('something.thisIsNotAValidToken.abcdefabcdef');
	}

	public function testToString(): void
	{
		$token = new Token(1234567890, 'someId', 'someKey');

		$this->assertSame('1234567890.someId.someKey', $token->toString());
		$this->assertSame('1234567890.someId.someKey', (string)$token);

		// without the key
		$this->assertSame('1234567890.someId', $token->toString(key: false));
	}

	public function testToStringWithoutKey(): void
	{
		$token = new Token(1234567890, 'someId');

		// a keyless token never appends a key, regardless of $key
		$this->assertSame('1234567890.someId', $token->toString());
		$this->assertSame('1234567890.someId', $token->toString(key: false));
	}
}
