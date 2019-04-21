<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Cache\Value
 */
class ValueTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::created
     */
    public function testCreated()
    {
        $value = new Value('foo');
        $this->assertEquals(time(), $value->created());

        $value = new Value('foo', 0, 1000);
        $this->assertEquals(1000, $value->created());
    }

    /**
     * @covers ::expires
     */
    public function testExpires()
    {
        $value = new Value('foo');
        $this->assertNull($value->expires());

        $value = new Value('foo', 0, 10000);
        $this->assertNull($value->expires());

        $value = new Value('foo', 1000, 0);
        $this->assertEquals(1000 * 60, $value->expires());

        $value = new Value('foo', 1000, 10000);
        $this->assertEquals(10000 + 1000 * 60, $value->expires());
    }

    /**
     * @covers ::fromArray
     * @covers ::toArray
     */
    public function testArrayConversion()
    {
        $data = [
            'created' => 10000,
            'minutes' => 0,
            'value'   => 'foo'
        ];
        $value = Value::fromArray($data);
        $this->assertEquals(10000, $value->created());
        $this->assertNull($value->expires());
        $this->assertEquals('foo', $value->value());
        $this->assertEquals($data, $value->toArray());

        $time = time();
        $data = [
            'created' => null,
            'minutes' => 1000,
            'value'   => ['this is' => 'an array']
        ];
        $value = Value::fromArray($data);
        $this->assertEquals($time, $value->created());
        $this->assertEquals($time + 1000 * 60, $value->expires());
        $this->assertEquals(['this is' => 'an array'], $value->value());
        $this->assertEquals([
            'created' => $time,
            'minutes' => 1000,
            'value'   => ['this is' => 'an array']
        ], $value->toArray());

        $data = [
            'created' => 10000,
            'minutes' => null,
            'value'   => 'foo'
        ];
        $value = Value::fromArray($data);
        $this->assertEquals(10000, $value->created());
        $this->assertNull($value->expires());
        $this->assertEquals('foo', $value->value());
        $this->assertEquals([
            'created' => 10000,
            'minutes' => 0,
            'value'   => 'foo'
        ], $value->toArray());
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArrayInvalid()
    {
        $this->expectException(\TypeError::class);

        $data = [
            'created' => 'invalid',
            'minutes' => 'invalid',
            'value'   => 'foo'
        ];
        $value = Value::fromArray($data);
    }

    /**
     * @covers ::fromJson
     * @covers ::toJson
     */
    public function testJsonConversion()
    {
        $data = json_encode([
            'created' => 10000,
            'minutes' => 0,
            'value'   => 'foo'
        ]);
        $value = Value::fromJson($data);
        $this->assertEquals(10000, $value->created());
        $this->assertNull($value->expires());
        $this->assertEquals('foo', $value->value());
        $this->assertEquals($data, $value->toJson());

        $time = time();
        $data = json_encode([
            'created' => null,
            'minutes' => 1000,
            'value'   => ['this is' => 'an array']
        ]);
        $value = Value::fromJson($data);
        $this->assertEquals($time, $value->created());
        $this->assertEquals($time + 1000 * 60, $value->expires());
        $this->assertEquals(['this is' => 'an array'], $value->value());
        $this->assertEquals(json_encode([
            'created' => $time,
            'minutes' => 1000,
            'value'   => ['this is' => 'an array']
        ]), $value->toJson());

        $data = json_encode([
            'created' => 10000,
            'minutes' => null,
            'value'   => 'foo'
        ]);
        $value = Value::fromJson($data);
        $this->assertEquals(10000, $value->created());
        $this->assertNull($value->expires());
        $this->assertEquals('foo', $value->value());
        $this->assertEquals(json_encode([
            'created' => 10000,
            'minutes' => 0,
            'value'   => 'foo'
        ]), $value->toJson());

        $this->assertNull(Value::fromJson('gibberish'));

        $data = json_encode([
            'created' => 'invalid',
            'minutes' => 'invalid',
            'value'   => 'foo'
        ]);
        $this->assertNull(Value::fromJson($data));
    }

    /**
     * @covers ::value
     */
    public function testValue()
    {
        $value = new Value('foo');
        $this->assertEquals('foo', $value->value());

        $value = new Value(['this is' => 'an array']);
        $this->assertEquals(['this is' => 'an array'], $value->value());

        $value = new Value(12345);
        $this->assertEquals(12345, $value->value());
    }
}
