<?php

namespace Kirby\Http;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Idn::class)]
class IdnTest extends TestCase
{
	public function testDecode(): void
	{
		$this->assertSame('exämple.com', Idn::decode('xn--exmple-cua.com'));
		$this->assertFalse(Idn::decode('xn--invalid--.com'));
		$this->assertFalse(Idn::decode(''));
	}

	public function testDecodeEmail(): void
	{
		$this->assertSame('test@example.com', Idn::decodeEmail('test@example.com'));
		$this->assertSame('test@exämple.com', Idn::decodeEmail('test@exämple.com'));
		$this->assertSame('test@exämple.com', Idn::decodeEmail('test@xn--exmple-cua.com'));
		$this->assertSame('"a@b"@exämple.com', Idn::decodeEmail('"a@b"@xn--exmple-cua.com'));
	}

	public function testDecodeEmailWithInvalidDomain(): void
	{
		$this->assertSame('xn--test', Idn::decodeEmail('xn--test'));
		$this->assertSame('xn--test@', Idn::decodeEmail('xn--test@'));
		$this->assertSame('test@xn--invalid--.com', Idn::decodeEmail('test@xn--invalid--.com'));
	}

	public function testEncode(): void
	{
		$this->assertSame('xn--exmple-cua.com', Idn::encode('exämple.com'));
		$this->assertFalse(Idn::encode('-ä-.com'));
		$this->assertFalse(Idn::encode(''));
	}

	public function testEncodeEmail(): void
	{
		$this->assertSame('test@example.com', Idn::encodeEmail('test@example.com'));
		$this->assertSame('test@xn--exmple-cua.com', Idn::encodeEmail('test@xn--exmple-cua.com'));
		$this->assertSame('test@xn--exmple-cua.com', Idn::encodeEmail('test@exämple.com'));
		$this->assertSame('"a@b"@xn--exmple-cua.com', Idn::encodeEmail('"a@b"@exämple.com'));
	}

	public function testEncodeEmailWithInvalidDomain(): void
	{
		$this->assertSame('tëst', Idn::encodeEmail('tëst'));
		$this->assertSame('tëst@', Idn::encodeEmail('tëst@'));
		$this->assertSame('test@-ä-.com', Idn::encodeEmail('test@-ä-.com'));
	}
}
