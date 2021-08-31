<?php

namespace Kirby\Cms;

class TimestampHelpersTest extends TestCase
{
    public function testInvalidDate(): void
    {
        $result = timestamp('2017-18-43');
        $this->assertSame(null, $result);
    }

    public function testWithoutStep(): void
    {
        $result = date('Y-m-d H:i:s', timestamp('2021-01-05'));
        $this->assertSame('2021-01-05 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-01-05 19:30:15'));
        $this->assertSame('2021-01-05 19:30:15', $result);
    }

    public function testStep10Seconds(): void
    {
        $result = date('Y-m-d H:i:s', timestamp('2021-08-18 00:00:00', ['unit' => 'second', 'size' => 10]));
        $this->assertSame('2021-08-18 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-18 19:27:13', ['unit' => 'second', 'size' => 10]));
        $this->assertSame('2021-08-18 19:27:10', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-18 10:59:59', ['unit' => 'second', 'size' => 10]));
        $this->assertSame('2021-08-18 11:00:00', $result);
    }

    public function testStep30Seconds(): void
    {
        $result = date('H:i:s', timestamp('2000-01-11 22:35:15', ['unit' => 'second', 'size' => 30]));
        $this->assertSame('22:35:30', $result);
    }

    public function testStep5Minutes(): void
    {
        $result = date('Y-m-d H:i:s', timestamp('2021-08-18 00:00:00', 5));
        $this->assertSame('2021-08-18 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-18 19:27:15', 5));
        $this->assertSame('2021-08-18 19:25:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-18 10:59:00', 5));
        $this->assertSame('2021-08-18 11:00:00', $result);
    }

    public function testStep2Hours(): void
    {
        $result = date('Y-m-d H:i:s', timestamp('2021-08-18 00:00:00', ['unit' => 'hour', 'size' => 2]));
        $this->assertSame('2021-08-18 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-18 19:27:15', ['unit' => 'hour', 'size' => 2]));
        $this->assertSame('2021-08-18 20:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-18 23:59:00', ['unit' => 'hour', 'size' => 2]));
        $this->assertSame('2021-08-19 00:00:00', $result);
    }

    public function testStep1Day(): void
    {
        $result = date('Y-m-d H:i:s', timestamp('2021-08-17 00:00:00', 'day'));
        $this->assertSame('2021-08-17 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-17 19:27:15', 'day'));
        $this->assertSame('2021-08-18 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-31 23:59:00', 'day'));
        $this->assertSame('2021-09-01 00:00:00', $result);
    }

    public function testStep1Month(): void
    {
        $result = date('Y-m-d H:i:s', timestamp('2021-08-17 00:00:00', 'month'));
        $this->assertSame('2021-09-01 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-13 19:27:15', 'month'));
        $this->assertSame('2021-08-01 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-31 23:59:00', 'month'));
        $this->assertSame('2021-09-01 00:00:00', $result);
    }

    public function testStep1Year(): void
    {
        $result = date('Y-m-d H:i:s', timestamp('2021-05-17 00:00:00', 'year'));
        $this->assertSame('2021-01-01 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-13 19:27:15', 'year'));
        $this->assertSame('2022-01-01 00:00:00', $result);

        $result = date('Y-m-d H:i:s', timestamp('2021-08-31 23:59:00', 'year'));
        $this->assertSame('2022-01-01 00:00:00', $result);
    }
}
