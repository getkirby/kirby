<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class IdnTest extends TestCase
{
    public function testDecodeEmail()
    {
        $this->assertSame('test@example.com', Idn::decodeEmail('test@example.com'));
        $this->assertSame('test@exämple.com', Idn::decodeEmail('test@exämple.com'));
        $this->assertSame('test@exämple.com', Idn::decodeEmail('test@xn--exmple-cua.com'));
    }

    public function testEncodeEmail()
    {
        $this->assertSame('test@example.com', Idn::encodeEmail('test@example.com'));
        $this->assertSame('test@xn--exmple-cua.com', Idn::encodeEmail('test@xn--exmple-cua.com'));
        $this->assertSame('test@xn--exmple-cua.com', Idn::encodeEmail('test@exämple.com'));
    }
}
