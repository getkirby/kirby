<?php

namespace Kirby\Cms;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Cms\Responder
 */
class ResponderTest extends TestCase
{
    public function setUp(): void
    {
        $this->kirby([
            'urls' => [
                'index' => 'https://getkirby.test'
            ]
        ]);
    }

    /**
     * @covers ::expires
     */
    public function testExpires()
    {
        $responder = new Responder();
        $this->assertNull($responder->expires());

        // minutes
        $this->assertSame($responder, $responder->expires(60 * 24));
        $this->assertSame(MockTime::$time + 60 * 60 * 24, $responder->expires());

        // explicit timestamp
        $this->assertSame($responder, $responder->expires(1234567890));
        $this->assertSame(1234567890, $responder->expires());

        // shorter expiry is always possible
        $this->assertSame($responder, $responder->expires(1234567889));
        $this->assertSame(1234567889, $responder->expires());

        // longer expiry only explicitly
        $this->assertSame($responder, $responder->expires(1234567890));
        $this->assertSame(1234567889, $responder->expires());

        $this->assertSame($responder, $responder->expires(1234567890, true));
        $this->assertSame(1234567890, $responder->expires());

        // getter on null input
        $this->assertSame(1234567890, $responder->expires(null));
        $this->assertSame(1234567890, $responder->expires());

        // but unset explicitly
        $this->assertSame($responder, $responder->expires(null, true));
        $this->assertNull($responder->expires());

        // string value parsing
        $this->assertSame($responder, $responder->expires('2021-01-01'));
        $this->assertSame(1609459200, $responder->expires());

        // rules still apply to string values
        $this->assertSame($responder, $responder->expires('2020-12-31'));
        $this->assertSame(1609372800, $responder->expires());
        $this->assertSame($responder, $responder->expires('2021-01-01'));
        $this->assertSame(1609372800, $responder->expires());
        $this->assertSame($responder, $responder->expires('2021-01-01', true));
        $this->assertSame(1609459200, $responder->expires());
    }

    /**
     * @covers ::expires
     */
    public function testExpiresInvalidString()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid time string "abcde"');

        $responder = new Responder();
        $responder->expires('abcde');
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArray()
    {
        $responder = new Responder();
        $responder->fromArray([
            'body'    => 'Lorem ipsum',
            'expires' => 1234567890,
            'code'    => 301,
            'headers' => ['Location' => 'https://example.com'],
            'type'    => 'text/plain'
        ]);

        $this->assertSame('Lorem ipsum', $responder->body());
        $this->assertSame(1234567890, $responder->expires());
        $this->assertSame(301, $responder->code());
        $this->assertSame(['Location' => 'https://example.com'], $responder->headers());
        $this->assertSame('text/plain', $responder->type());
    }
}
