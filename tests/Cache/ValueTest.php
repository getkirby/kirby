<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/mocks.php';

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
        $this->assertSame(time(), $value->created());

        $value = new Value('foo', 0, 1000);
        $this->assertSame(1000, $value->created());
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
        $this->assertSame(1000 * 60, $value->expires());

        $value = new Value('foo', 1000, 10000);
        $this->assertSame(10000 + 1000 * 60, $value->expires());

        $value = new Value('foo', 1234567890, 0);
        $this->assertSame(1234567890, $value->expires());

        $value = new Value('foo', 1234567890, 10000);
        $this->assertSame(1234567890, $value->expires());
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
        $this->assertSame(10000, $value->created());
        $this->assertNull($value->expires());
        $this->assertSame('foo', $value->value());
        $this->assertSame($data, $value->toArray());

        $data = [
            'created' => 10000,
            'minutes' => 1234567890,
            'value'   => 'foo'
        ];
        $value = Value::fromArray($data);
        $this->assertSame(10000, $value->created());
        $this->assertSame(1234567890, $value->expires());
        $this->assertSame('foo', $value->value());
        $this->assertSame($data, $value->toArray());

        $time = time();
        $data = [
            'created' => null,
            'minutes' => 1000,
            'value'   => ['this is' => 'an array']
        ];
        $value = Value::fromArray($data);
        $this->assertSame($time, $value->created());
        $this->assertSame($time + 1000 * 60, $value->expires());
        $this->assertSame(['this is' => 'an array'], $value->value());
        $this->assertSame([
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
        $this->assertSame(10000, $value->created());
        $this->assertNull($value->expires());
        $this->assertSame('foo', $value->value());
        $this->assertSame([
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
        $this->assertSame(10000, $value->created());
        $this->assertNull($value->expires());
        $this->assertSame('foo', $value->value());
        $this->assertSame($data, $value->toJson());

        $time = time();
        $data = json_encode([
            'created' => null,
            'minutes' => 1000,
            'value'   => ['this is' => 'an array']
        ]);
        $value = Value::fromJson($data);
        $this->assertSame($time, $value->created());
        $this->assertSame($time + 1000 * 60, $value->expires());
        $this->assertSame(['this is' => 'an array'], $value->value());
        $this->assertSame(json_encode([
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
        $this->assertSame(10000, $value->created());
        $this->assertNull($value->expires());
        $this->assertSame('foo', $value->value());
        $this->assertSame(json_encode([
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
        $this->assertSame('foo', $value->value());

        $value = new Value(['this is' => 'an array']);
        $this->assertSame(['this is' => 'an array'], $value->value());

        $value = new Value(12345);
        $this->assertSame(12345, $value->value());
    }
}
