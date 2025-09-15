<?php

namespace Kirby\Http;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Idn::class)]
class IdnTest extends TestCase
{
	public function testDecodeEmail(): void
	{
		$this->assertSame('test@example.com', Idn::decodeEmail('test@example.com'));
		$this->assertSame('test@exämple.com', Idn::decodeEmail('test@exämple.com'));
		$this->assertSame('test@exämple.com', Idn::decodeEmail('test@xn--exmple-cua.com'));
	}

	public function testEncodeEmail(): void
	{
		$this->assertSame('test@example.com', Idn::encodeEmail('test@example.com'));
		$this->assertSame('test@xn--exmple-cua.com', Idn::encodeEmail('test@xn--exmple-cua.com'));
		$this->assertSame('test@xn--exmple-cua.com', Idn::encodeEmail('test@exämple.com'));
	}
}
