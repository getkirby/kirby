<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class VisitorTest extends TestCase
{
    public function testVisitorDefaults()
    {
        $visitor = new Visitor;

        $this->assertEquals('', $visitor->ip());
        $this->assertEquals('', $visitor->userAgent());
        $this->assertInstanceOf('Kirby\Http\Acceptance\Language', $visitor->acceptedLanguage());
        $this->assertInstanceOf('Kirby\Http\Acceptance\MimeType', $visitor->acceptedMimeType());
    }

    public function testVisitorWithArguments()
    {
        $visitor = new Visitor([
            'ip'               => '192.168.1.1',
            'userAgent'        => 'Kirby',
            'acceptedLanguage' => 'en-US',
            'acceptedMimeType' => 'text/html'
        ]);

        $this->assertEquals('192.168.1.1', $visitor->ip());
        $this->assertEquals('Kirby', $visitor->userAgent());
        $this->assertInstanceOf('Kirby\Http\Acceptance\Language', $visitor->acceptedLanguage());
        $this->assertEquals('en_US', $visitor->acceptedLanguage()->locale());
        $this->assertInstanceOf('Kirby\Http\Acceptance\MimeType', $visitor->acceptedMimeType());
        $this->assertEquals('text/html', $visitor->acceptedMimeType()->value());
    }

    public function testIp()
    {
        $visitor = new Visitor;
        $this->assertEquals(null, $visitor->ip());
        $this->assertInstanceOf(Visitor::class, $visitor->ip('192.168.1.1'));
        $this->assertEquals('192.168.1.1', $visitor->ip());
    }

    public function testUserAgent()
    {
        $visitor = new Visitor;
        $this->assertInstanceOf(Visitor::class, $visitor->userAgent('Kirby'));
        $this->assertEquals('Kirby', $visitor->userAgent());
    }

    public function testAcceptedLanguage()
    {
        $visitor = new Visitor;
        $this->assertInstanceOf('Kirby\Http\Acceptance\Language', $visitor->acceptedLanguage());
    }

    public function testAcceptedMimeType()
    {
        $visitor = new Visitor;
        $this->assertInstanceOf('Kirby\Http\Acceptance\MimeType', $visitor->acceptedMimeType());
    }

    public function testAccepts()
    {
        $visitor = new Visitor;
        $this->assertFalse($visitor->accepts('text/html'));

        $visitor = new Visitor(['acceptedMimeType' => 'text/html']);
        $this->assertTrue($visitor->accepts('text/html'));
    }

}
