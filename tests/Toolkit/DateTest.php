<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\Date
 */
class DateTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__constructWithString()
    {
        $date = new Date('2021-12-12');
        $this->assertSame('2021-12-12', $date->format('Y-m-d'));
    }

    /**
     * @covers ::__construct
     */
    public function test__constructWithInt()
    {
        $date = new Date(strtotime('2021-12-12'));
        $this->assertSame('2021-12-12', $date->format('Y-m-d'));
    }

    /**
     * @covers ::__construct
     */
    public function test__constructWithDate()
    {
        $date = new Date(new Date('2021-12-12'));
        $this->assertSame('2021-12-12', $date->format('Y-m-d'));
    }

    /**
     * @covers ::__toString
     */
    public function test__toString()
    {
        $date = new Date('2021-12-12 12:12:12');
        $this->assertSame('2021-12-12 12:12:12+00:00', (string)$date);
    }

    /**
     * @covers ::compare
     */
    public function testCompare()
    {
        $date = new Date('2021-12-12');
        $diff = $date->compare('2021-12-10');

        $this->assertInstanceOf('DateInterval', $diff);
        $this->assertSame(1, $diff->invert);
        $this->assertSame(2, $diff->days);
    }

    /**
     * @covers ::day
     */
    public function testDay()
    {
        $date = new Date('2021-12-12');
        $this->assertSame(12, $date->day());
        $this->assertSame(13, $date->day(13));
        $this->assertSame('2021-12-13', $date->format('Y-m-d'));
    }

    /**
     * @covers ::hour
     */
    public function testHour()
    {
        $date = new Date('12:12');
        $this->assertSame(12, $date->hour());
        $this->assertSame(13, $date->hour(13));
        $this->assertSame('13:12', $date->format('H:i'));
    }

    /**
     * @covers ::is
     */
    public function testIs()
    {
        $date = new Date('2021-12-12');
        $this->assertTrue($date->is('2021-12-12'));
        $this->assertFalse($date->is('2021-12-13'));
    }

    /**
     * @covers ::isAfter
     */
    public function testIsAfter()
    {
        $date = new Date('2021-12-12');
        $this->assertTrue($date->isAfter('2021-12-11'));
        $this->assertFalse($date->isAfter('2021-12-13'));
    }

    /**
     * @covers ::isBefore
     */
    public function testIsBefore()
    {
        $date = new Date('2021-12-12');
        $this->assertFalse($date->isBefore('2021-12-11'));
        $this->assertTrue($date->isBefore('2021-12-13'));
    }

    /**
     * @covers ::isBetween
     */
    public function testIsBetween()
    {
        $date = new Date('2021-12-12');
        $this->assertTrue($date->isBetween('2021-12-12', '2021-12-12'));
        $this->assertTrue($date->isBetween('2021-12-11', '2021-12-13'));
        $this->assertFalse($date->isBetween('2021-12-13', '2021-12-14'));
    }

    /**
     * @covers ::isMax
     */
    public function testIsMax()
    {
        $date = new Date('2021-12-12');
        $this->assertTrue($date->isMax('2021-12-12'));
        $this->assertFalse($date->isMax('2021-12-11'));
    }

    /**
     * @covers ::isMin
     */
    public function testIsMin()
    {
        $date = new Date('2021-12-12');
        $this->assertTrue($date->isMin('2021-12-12'));
        $this->assertFalse($date->isMin('2021-12-13'));
    }

    /**
     * @covers ::microsecond
     */
    public function testMicrosecond()
    {
        $date = new Date('2021-12-12');
        $date->modify('+500 ms');

        $this->assertSame(500000, $date->microsecond());
    }

    /**
     * @covers ::millisecond
     */
    public function testMillisecond()
    {
        $date = new Date('2021-12-12');
        $date->modify('+500 ms');

        $this->assertSame(500, $date->millisecond());
    }

    /**
     * @covers ::minute
     */
    public function testMinute()
    {
        $date = new Date('12:12');
        $this->assertSame(12, $date->minute());
        $this->assertSame(13, $date->minute(13));
        $this->assertSame('12:13', $date->format('H:i'));
    }

    /**
     * @covers ::month
     */
    public function testMonth()
    {
        $date = new Date('2021-12-12');
        $this->assertSame(12, $date->month());
        $this->assertSame(11, $date->month(11));
        $this->assertSame('2021-11-12', $date->format('Y-m-d'));
    }

    /**
     * @covers ::now
     */
    public function testNow()
    {
        $date = Date::now();

        $this->assertSame(date('Y-m-d H:i:s'), $date->format('Y-m-d H:i:s'));
        $this->assertInstanceOf(Date::class, $date);
    }

    /**
     * @covers ::optional
     */
    public function testOptional()
    {
        $this->assertNull(Date::optional(null));
        $this->assertNull(Date::optional(''));
        $this->assertNull(Date::optional('invalid date'));
        $this->assertInstanceOf(Date::class, Date::optional('2021-12-12'));
    }

    /**
     * @covers ::round
     * @dataProvider roundProvider
     */
    public function testRound(string $unit, int $size, string $input, string $expected)
    {
        $date = new Date($input);
        $this->assertSame($date, $date->round($unit, $size));
        $this->assertSame($expected, $date->format('Y-m-d H:i:s'));
    }

    public function roundProvider(): array
    {
        return [
            '1s: no change'  => ['second', 1, '2020-02-29 16:05:15', '2020-02-29 16:05:15'],
            '5s: no change'  => ['second', 5, '2020-02-29 16:05:15', '2020-02-29 16:05:15'],
            '5s: floor'      => ['second', 5, '2020-02-29 16:05:12', '2020-02-29 16:05:10'],
            '5s: ceil'       => ['second', 5, '2020-02-29 16:05:13', '2020-02-29 16:05:15'],
            '5s: carry'      => ['second', 5, '2020-02-29 16:59:58', '2020-02-29 17:00:00'],
            '1m: no change'  => ['minute', 1, '2020-02-29 16:05:15', '2020-02-29 16:05:00'],
            '1m: ceil sub'   => ['minute', 1, '2020-02-29 16:05:55', '2020-02-29 16:06:00'],
            '15m: no change' => ['minute', 1, '2020-02-29 16:07:15', '2020-02-29 16:07:00'],
            '15m: floor'     => ['minute', 15, '2020-02-29 16:07:15', '2020-02-29 16:00:00'],
            '15m: ceil'      => ['minute', 15, '2020-02-29 16:08:15', '2020-02-29 16:15:00'],
            '15m: ceil sub'  => ['minute', 15, '2020-02-29 16:07:31', '2020-02-29 16:15:00'],
            '15m: carry'     => ['minute', 15, '2020-02-29 23:53:15', '2020-03-01 00:00:00'],
            '1h: no change'  => ['hour', 1, '2020-02-29 16:05:15', '2020-02-29 16:00:00'],
            '1h: ceil sub'   => ['hour', 1, '2020-02-29 16:59:15', '2020-02-29 17:00:00'],
            '4h: no change'  => ['hour', 4, '2020-02-29 16:05:15', '2020-02-29 16:00:00'],
            '4h: floor'      => ['hour', 4, '2020-02-29 17:00:15', '2020-02-29 16:00:00'],
            '4h: ceil'       => ['hour', 4, '2020-02-29 15:08:15', '2020-02-29 16:00:00'],
            '4h: ceil sub'   => ['hour', 4, '2020-02-29 14:07:31', '2020-02-29 16:00:00'],
            '4h: carry'      => ['hour', 4, '2020-02-29 23:53:15', '2020-03-01 00:00:00'],
            '1D: no change'  => ['day', 1, '2020-02-29 09:05:15', '2020-02-29 00:00:00'],
            '1D: ceil sub'   => ['day', 1, '2020-02-29 16:05:15', '2020-03-01 00:00:00'],
            '1M: no change'  => ['month', 1, '2020-02-14 09:05:15', '2020-02-01 00:00:00'],
            '1M: ceil sub'   => ['month', 1, '2020-02-29 16:05:15', '2020-03-01 00:00:00'],
            '1Y: no change'  => ['year', 1, '2020-02-14 09:05:15', '2020-01-01 00:00:00'],
            '1Y: ceil sub'   => ['year', 1, '2020-09-29 16:05:15', '2021-01-01 00:00:00'],

            'kirby/issues/3642' => ['minute', 5, '2021-08-18 10:59:00', '2021-08-18 11:00:00'],
        ];
    }

    /**
     * @covers ::round
     * @dataProvider roundUnsupportedSizeProvider
     */
    public function testRoundUnsupportedSize(string $unit, int $size)
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid rounding size for ' . $unit);

        $date = new Date('2020-01-01');
        $date->round($unit, $size);
    }

    public function roundUnsupportedSizeProvider(): array
    {
        return [
            ['second', 7],
            ['minute', 7],
            ['hour', 5],
            ['day', 2],
            ['month', 2],
            ['year', 2],
        ];
    }

    /**
     * @covers ::round
     * @covers ::validateUnit
     */
    public function testRoundUnsupportedUnit()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid rounding unit');

        $date = new Date('2020-01-01');
        $date->round('foo', 1);
    }

    /**
     * @covers ::second
     */
    public function testSecond()
    {
        $date = new Date('12:12:12');
        $this->assertSame(12, $date->second());
        $this->assertSame(13, $date->second(13));
        $this->assertSame('12:12:13', $date->format('H:i:s'));
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        $date = new Date('2021-12-12');

        // overwrite with timestamp
        $timestamp = strtotime('2021-12-13');
        $date->set($timestamp);
        $this->assertSame('2021-12-13', $date->format('Y-m-d'));

        // overwrite with string
        $date->set('2021-12-13');
        $this->assertSame('2021-12-13', $date->format('Y-m-d'));

        // overwrite with date object
        $date->set(new Date('2021-12-13'));
        $this->assertSame('2021-12-13', $date->format('Y-m-d'));
    }

    /**
     * @covers ::stepConfig
     */
    public function testStepConfig()
    {
        $config = Date::stepConfig();

        $this->assertSame([
            'size' => 1,
            'unit' => 'day'
        ], $config);
    }

    /**
     * @covers ::stepConfig
     */
    public function testStepConfigWithArray()
    {
        $config = Date::stepConfig([
            'size' => 5,
            'unit' => 'Hour'
        ]);

        $this->assertSame([
            'size' => 5,
            'unit' => 'hour'
        ], $config);
    }

    /**
     * @covers ::stepConfig
     */
    public function testStepConfigWithInt()
    {
        $config = Date::stepConfig(5);

        $this->assertSame([
            'size' => 5,
            'unit' => 'day'
        ], $config);
    }

    /**
     * @covers ::stepConfig
     */
    public function testStepConfigWithInvalidInput()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid input');

        Date::stepConfig(new Date());
    }

    /**
     * @covers ::stepConfig
     */
    public function testStepConfigWithString()
    {
        $config = Date::stepConfig('Minute');

        $this->assertSame([
            'size' => 1,
            'unit' => 'minute'
        ], $config);
    }

    /**
     * @covers ::stepConfig
     */
    public function testStepConfigWithCustomDefault()
    {
        $config = Date::stepConfig(null, $default = [
            'size' => 5,
            'unit' => 'month'
        ]);

        $this->assertSame($default, $config);
    }

    /**
     * @covers ::time
     */
    public function testTime()
    {
        $date = new Date('2021-12-12 12:12:12');
        $this->assertSame('12:12:12', $date->time());
    }

    /**
     * @covers ::timestamp
     */
    public function testTimestamp()
    {
        $date = new Date('2021-12-12');
        $timestamp = strtotime('2021-12-12');

        $this->assertSame($timestamp, $date->timestamp());
    }

    /**
     * @covers ::timezone
     */
    public function testTimezone()
    {
        $date = new Date();
        $this->assertInstanceOf('DateTimeZone', $date->timezone());
    }

    /**
     * @covers ::today
     */
    public function testToday()
    {
        $date = Date::today();
        $timestamp = strtotime('today');
        $this->assertSame($timestamp, $date->timestamp());
    }

    /**
     * @covers ::toString
     */
    public function testToStringModeDate()
    {
        // with timezone
        $date = new Date('2021-12-12');
        $this->assertSame('2021-12-12+00:00', $date->toString('date'));

        // without timezone
        $this->assertSame('2021-12-12', $date->toString('date', false));
    }

    /**
     * @covers ::toString
     */
    public function testToStringModeDatetime()
    {
        // with timezone
        $date = new Date('2021-12-12 12:12:12');
        $this->assertSame('2021-12-12 12:12:12+00:00', $date->toString());
        $this->assertSame('2021-12-12 12:12:12+00:00', $date->toString('datetime'));

        // without timezone
        $this->assertSame('2021-12-12 12:12:12', $date->toString('datetime', false));
    }

    /**
     * @covers ::toString
     */
    public function testToStringModeTime()
    {
        // with timezone
        $date = new Date('12:12:12');
        $this->assertSame('12:12:12+00:00', $date->toString('time'));

        // without timezone
        $this->assertSame('12:12:12', $date->toString('time', false));
    }

    /**
     * @covers ::toString
     */
    public function testToStringWithInvalidMode()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid mode');

        $date = new Date('12:12:12');
        $date->toString('foo');
    }

    /**
     * @covers ::year
     */
    public function testYear()
    {
        $date = new Date('2021-12-12');
        $this->assertSame(2021, $date->year());
        $this->assertSame(2022, $date->year(2022));
        $this->assertSame('2022-12-12', $date->format('Y-m-d'));
    }
}
