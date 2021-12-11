<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\Date
 */
class DateTest extends TestCase
{
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
     */
    public function testRoundUnsupportedUnit()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid rounding unit');

        $date = new Date('2020-01-01');
        $date->round('foo', 1);
    }
}
