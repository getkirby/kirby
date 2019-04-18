<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    public function testSetup()
    {
        $value = new Value('foo');
        $this->assertEquals('foo', $value->value());
        $this->assertEquals(time(), $value->created());
        $this->assertEquals(time() + (2628000 * 60), $value->expires());
    }

    public function testSetupCustomDuration()
    {
        $value = new Value('foo', 100);
        $this->assertEquals(time() + (100 * 60), $value->expires());
    }

    public function testFromArray()
    {
        $value = Value::fromArray([
            'value'   => 'foo',
            'minutes' => 1,
            'created' => 0
        ]);

        $this->assertEquals('foo', $value->value());
        $this->assertEquals(0, $value->created());
        $this->assertEquals(60, $value->expires());
    }

    public function testFromJson()
    {
        $value = Value::fromJson(json_encode([
            'value'   => 'foo',
            'minutes' => 1,
            'created' => 0
        ]));

        $this->assertEquals('foo', $value->value());
        $this->assertEquals(0, $value->created());
        $this->assertEquals(60, $value->expires());
    }

    public function testFromInvalidJson()
    {
        $value = Value::fromJson('foo');
        $this->assertNull($value->value());
    }
}
