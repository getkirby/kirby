<?php

namespace Kirby\Toolkit;

use IntlDateFormatter;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\Date
 */
class DateTest extends TestCase
{
	public function tearDown(): void
	{
		App::destroy();
	}

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
	 * @covers ::formatWithHandler
	 */
	public function testFormatWithHandler()
	{
		$date = new Date('2020-01-29 01:01');

		// default handler (fallback to `date`)
		$this->assertSame($date->timestamp(), $date->formatWithHandler());
		$this->assertSame('29.01.2020', $date->formatWithHandler('d.m.Y'));

		// default handler (global app object)
		$app = new App([
			'options' => [
				'date' => [
					'handler' => 'intl'
				]
			]
		]);
		$app->setCurrentLanguage('en');
		$this->assertSame($date->timestamp(), $date->formatWithHandler());
		$this->assertSame('29/1/2020 01:01', $date->formatWithHandler('d/M/yyyy HH:mm'));

		// explicit `date` handler
		$this->assertSame($date->timestamp(), $date->formatWithHandler(null, 'date'));
		$this->assertSame('29.01.2020', $date->formatWithHandler('d.m.Y', 'date'));

		// `intl` handler
		$this->assertSame($date->timestamp(), $date->formatWithHandler(null, 'intl'));
		$this->assertSame('29/1/2020 01:01', $date->formatWithHandler('d/M/yyyy HH:mm', 'intl'));

		// passing custom `intl` handler
		$formatter = new IntlDateFormatter(
			'en-US',
			IntlDateFormatter::LONG,
			IntlDateFormatter::SHORT
		);
		// @todo remove str_replace when IntlDateFormatter doesn't result
		// in different spaces depending on the system its running on
		$result = $date->formatWithHandler($formatter);
		$result = str_replace("\xE2\x80\xAF", ' ', $result);
		$this->assertSame('January 29, 2020 at 1:01 AM', $result);

		// `strftime` handler
		$this->assertSame($date->timestamp(), $date->formatWithHandler(null, 'strftime'));
		$this->assertSame('29.01.2020', $date->formatWithHandler('%d.%m.%Y', 'strftime'));
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

	public static function roundProvider(): array
	{
		return [
			'1s: no change UTC'  => ['second', 1, '2020-02-29 16:05:15', 'UTC', '2020-02-29 16:05:15'],
			'5s: no change UTC'  => ['second', 5, '2020-02-29 16:05:15', 'UTC', '2020-02-29 16:05:15'],
			'5s: floor UTC'      => ['second', 5, '2020-02-29 16:05:12', 'UTC', '2020-02-29 16:05:10'],
			'5s: ceil UTC'       => ['second', 5, '2020-02-29 16:05:13', 'UTC', '2020-02-29 16:05:15'],
			'5s: carry UTC'      => ['second', 5, '2020-02-29 16:59:58', 'UTC', '2020-02-29 17:00:00'],
			'1m: no change UTC'  => ['minute', 1, '2020-02-29 16:05:15', 'UTC', '2020-02-29 16:05:00'],
			'1m: ceil sub UTC'   => ['minute', 1, '2020-02-29 16:05:55', 'UTC', '2020-02-29 16:06:00'],
			'15m: no change UTC' => ['minute', 1, '2020-02-29 16:07:15', 'UTC', '2020-02-29 16:07:00'],
			'15m: floor UTC'     => ['minute', 15, '2020-02-29 16:07:15', 'UTC', '2020-02-29 16:00:00'],
			'15m: ceil UTC'      => ['minute', 15, '2020-02-29 16:08:15', 'UTC', '2020-02-29 16:15:00'],
			'15m: ceil sub UTC'  => ['minute', 15, '2020-02-29 16:07:31', 'UTC', '2020-02-29 16:15:00'],
			'15m: carry UTC'     => ['minute', 15, '2020-02-29 23:53:15', 'UTC', '2020-03-01 00:00:00'],
			'1h: no change UTC'  => ['hour', 1, '2020-02-29 16:05:15', 'UTC', '2020-02-29 16:00:00'],
			'1h: ceil sub UTC'   => ['hour', 1, '2020-02-29 16:59:15', 'UTC', '2020-02-29 17:00:00'],
			'4h: no change UTC'  => ['hour', 4, '2020-02-29 16:05:15', 'UTC', '2020-02-29 16:00:00'],
			'4h: floor UTC'      => ['hour', 4, '2020-02-29 17:00:15', 'UTC', '2020-02-29 16:00:00'],
			'4h: ceil UTC'       => ['hour', 4, '2020-02-29 15:08:15', 'UTC', '2020-02-29 16:00:00'],
			'4h: ceil sub UTC'   => ['hour', 4, '2020-02-29 14:07:31', 'UTC', '2020-02-29 16:00:00'],
			'4h: carry UTC'      => ['hour', 4, '2020-02-29 23:53:15', 'UTC', '2020-03-01 00:00:00'],
			'1D: no change UTC'  => ['day', 1, '2020-02-29 09:05:15', 'UTC', '2020-02-29 00:00:00'],
			'1D: ceil sub UTC'   => ['day', 1, '2020-02-29 16:05:15', 'UTC', '2020-03-01 00:00:00'],
			'1M: no change UTC'  => ['month', 1, '2020-02-14 09:05:15', 'UTC', '2020-02-01 00:00:00'],
			'1M: ceil sub UTC'   => ['month', 1, '2020-02-29 16:05:15', 'UTC', '2020-03-01 00:00:00'],
			'1Y: no change UTC'  => ['year', 1, '2020-02-14 09:05:15', 'UTC', '2020-01-01 00:00:00'],
			'1Y: ceil sub UTC'   => ['year', 1, '2020-09-29 16:05:15', 'UTC', '2021-01-01 00:00:00'],

			'kirby/issues/3642 UTC' => ['minute', 5, '2021-08-18 10:59:00', 'UTC', '2021-08-18 11:00:00'],

			'1s: no change Europe/London'  => ['second', 1, '2020-02-29 16:05:15', 'Europe/London', '2020-02-29 16:05:15'],
			'5s: no change Europe/London'  => ['second', 5, '2020-02-29 16:05:15', 'Europe/London', '2020-02-29 16:05:15'],
			'5s: floor Europe/London'      => ['second', 5, '2020-02-29 16:05:12', 'Europe/London', '2020-02-29 16:05:10'],
			'5s: ceil Europe/London'       => ['second', 5, '2020-02-29 16:05:13', 'Europe/London', '2020-02-29 16:05:15'],
			'5s: carry Europe/London'      => ['second', 5, '2020-02-29 16:59:58', 'Europe/London', '2020-02-29 17:00:00'],
			'1m: no change Europe/London'  => ['minute', 1, '2020-02-29 16:05:15', 'Europe/London', '2020-02-29 16:05:00'],
			'1m: ceil sub Europe/London'   => ['minute', 1, '2020-02-29 16:05:55', 'Europe/London', '2020-02-29 16:06:00'],
			'15m: no change Europe/London' => ['minute', 1, '2020-02-29 16:07:15', 'Europe/London', '2020-02-29 16:07:00'],
			'15m: floor Europe/London'     => ['minute', 15, '2020-02-29 16:07:15', 'Europe/London', '2020-02-29 16:00:00'],
			'15m: ceil Europe/London'      => ['minute', 15, '2020-02-29 16:08:15', 'Europe/London', '2020-02-29 16:15:00'],
			'15m: ceil sub Europe/London'  => ['minute', 15, '2020-02-29 16:07:31', 'Europe/London', '2020-02-29 16:15:00'],
			'15m: carry Europe/London'     => ['minute', 15, '2020-02-29 23:53:15', 'Europe/London', '2020-03-01 00:00:00'],
			'1h: no change Europe/London'  => ['hour', 1, '2020-02-29 16:05:15', 'Europe/London', '2020-02-29 16:00:00'],
			'1h: ceil sub Europe/London'   => ['hour', 1, '2020-02-29 16:59:15', 'Europe/London', '2020-02-29 17:00:00'],
			'4h: no change Europe/London'  => ['hour', 4, '2020-02-29 16:05:15', 'Europe/London', '2020-02-29 16:00:00'],
			'4h: floor Europe/London'      => ['hour', 4, '2020-02-29 17:00:15', 'Europe/London', '2020-02-29 16:00:00'],
			'4h: ceil Europe/London'       => ['hour', 4, '2020-02-29 15:08:15', 'Europe/London', '2020-02-29 16:00:00'],
			'4h: ceil sub Europe/London'   => ['hour', 4, '2020-02-29 14:07:31', 'Europe/London', '2020-02-29 16:00:00'],
			'4h: carry Europe/London'      => ['hour', 4, '2020-02-29 23:53:15', 'Europe/London', '2020-03-01 00:00:00'],
			'1D: no change Europe/London'  => ['day', 1, '2020-02-29 09:05:15', 'Europe/London', '2020-02-29 00:00:00'],
			'1D: ceil sub Europe/London'   => ['day', 1, '2020-02-29 16:05:15', 'Europe/London', '2020-03-01 00:00:00'],
			'1M: no change Europe/London'  => ['month', 1, '2020-02-14 09:05:15', 'Europe/London', '2020-02-01 00:00:00'],
			'1M: ceil sub Europe/London'   => ['month', 1, '2020-02-29 16:05:15', 'Europe/London', '2020-03-01 00:00:00'],
			'1Y: no change Europe/London'  => ['year', 1, '2020-02-14 09:05:15', 'Europe/London', '2020-01-01 00:00:00'],
			'1Y: ceil sub Europe/London'   => ['year', 1, '2020-09-29 16:05:15', 'Europe/London', '2021-01-01 00:00:00'],

			'kirby/issues/3642 Europe/London' => ['minute', 5, '2021-08-18 10:59:00', 'Europe/London', '2021-08-18 11:00:00'],

			'1s: no change Europe/Berlin'  => ['second', 1, '2020-02-29 16:05:15', 'Europe/Berlin', '2020-02-29 16:05:15'],
			'5s: no change Europe/Berlin'  => ['second', 5, '2020-02-29 16:05:15', 'Europe/Berlin', '2020-02-29 16:05:15'],
			'5s: floor Europe/Berlin'      => ['second', 5, '2020-02-29 16:05:12', 'Europe/Berlin', '2020-02-29 16:05:10'],
			'5s: ceil Europe/Berlin'       => ['second', 5, '2020-02-29 16:05:13', 'Europe/Berlin', '2020-02-29 16:05:15'],
			'5s: carry Europe/Berlin'      => ['second', 5, '2020-02-29 16:59:58', 'Europe/Berlin', '2020-02-29 17:00:00'],
			'1m: no change Europe/Berlin'  => ['minute', 1, '2020-02-29 16:05:15', 'Europe/Berlin', '2020-02-29 16:05:00'],
			'1m: ceil sub Europe/Berlin'   => ['minute', 1, '2020-02-29 16:05:55', 'Europe/Berlin', '2020-02-29 16:06:00'],
			'15m: no change Europe/Berlin' => ['minute', 1, '2020-02-29 16:07:15', 'Europe/Berlin', '2020-02-29 16:07:00'],
			'15m: floor Europe/Berlin'     => ['minute', 15, '2020-02-29 16:07:15', 'Europe/Berlin', '2020-02-29 16:00:00'],
			'15m: ceil Europe/Berlin'      => ['minute', 15, '2020-02-29 16:08:15', 'Europe/Berlin', '2020-02-29 16:15:00'],
			'15m: ceil sub Europe/Berlin'  => ['minute', 15, '2020-02-29 16:07:31', 'Europe/Berlin', '2020-02-29 16:15:00'],
			'15m: carry Europe/Berlin'     => ['minute', 15, '2020-02-29 23:53:15', 'Europe/Berlin', '2020-03-01 00:00:00'],
			'1h: no change Europe/Berlin'  => ['hour', 1, '2020-02-29 16:05:15', 'Europe/Berlin', '2020-02-29 16:00:00'],
			'1h: ceil sub Europe/Berlin'   => ['hour', 1, '2020-02-29 16:59:15', 'Europe/Berlin', '2020-02-29 17:00:00'],
			'4h: no change Europe/Berlin'  => ['hour', 4, '2020-02-29 16:05:15', 'Europe/Berlin', '2020-02-29 16:00:00'],
			'4h: floor Europe/Berlin'      => ['hour', 4, '2020-02-29 17:00:15', 'Europe/Berlin', '2020-02-29 16:00:00'],
			'4h: ceil Europe/Berlin'       => ['hour', 4, '2020-02-29 15:08:15', 'Europe/Berlin', '2020-02-29 16:00:00'],
			'4h: ceil sub Europe/Berlin'   => ['hour', 4, '2020-02-29 14:07:31', 'Europe/Berlin', '2020-02-29 16:00:00'],
			'4h: carry Europe/Berlin'      => ['hour', 4, '2020-02-29 23:53:15', 'Europe/Berlin', '2020-03-01 00:00:00'],
			'1D: no change Europe/Berlin'  => ['day', 1, '2020-02-29 09:05:15', 'Europe/Berlin', '2020-02-29 00:00:00'],
			'1D: ceil sub Europe/Berlin'   => ['day', 1, '2020-02-29 16:05:15', 'Europe/Berlin', '2020-03-01 00:00:00'],
			'1M: no change Europe/Berlin'  => ['month', 1, '2020-02-14 09:05:15', 'Europe/Berlin', '2020-02-01 00:00:00'],
			'1M: ceil sub Europe/Berlin'   => ['month', 1, '2020-02-29 16:05:15', 'Europe/Berlin', '2020-03-01 00:00:00'],
			'1Y: no change Europe/Berlin'  => ['year', 1, '2020-02-14 09:05:15', 'Europe/Berlin', '2020-01-01 00:00:00'],
			'1Y: ceil sub Europe/Berlin'   => ['year', 1, '2020-09-29 16:05:15', 'Europe/Berlin', '2021-01-01 00:00:00'],

			'kirby/issues/3642 Europe/Berlin' => ['minute', 5, '2021-08-18 10:59:00', 'Europe/Berlin', '2021-08-18 11:00:00'],

			'1s: no change America/New_York'  => ['second', 1, '2020-02-29 16:05:15', 'America/New_York', '2020-02-29 16:05:15'],
			'5s: no change America/New_York'  => ['second', 5, '2020-02-29 16:05:15', 'America/New_York', '2020-02-29 16:05:15'],
			'5s: floor America/New_York'      => ['second', 5, '2020-02-29 16:05:12', 'America/New_York', '2020-02-29 16:05:10'],
			'5s: ceil America/New_York'       => ['second', 5, '2020-02-29 16:05:13', 'America/New_York', '2020-02-29 16:05:15'],
			'5s: carry America/New_York'      => ['second', 5, '2020-02-29 16:59:58', 'America/New_York', '2020-02-29 17:00:00'],
			'1m: no change America/New_York'  => ['minute', 1, '2020-02-29 16:05:15', 'America/New_York', '2020-02-29 16:05:00'],
			'1m: ceil sub America/New_York'   => ['minute', 1, '2020-02-29 16:05:55', 'America/New_York', '2020-02-29 16:06:00'],
			'15m: no change America/New_York' => ['minute', 1, '2020-02-29 16:07:15', 'America/New_York', '2020-02-29 16:07:00'],
			'15m: floor America/New_York'     => ['minute', 15, '2020-02-29 16:07:15', 'America/New_York', '2020-02-29 16:00:00'],
			'15m: ceil America/New_York'      => ['minute', 15, '2020-02-29 16:08:15', 'America/New_York', '2020-02-29 16:15:00'],
			'15m: ceil sub America/New_York'  => ['minute', 15, '2020-02-29 16:07:31', 'America/New_York', '2020-02-29 16:15:00'],
			'15m: carry America/New_York'     => ['minute', 15, '2020-02-29 23:53:15', 'America/New_York', '2020-03-01 00:00:00'],
			'1h: no change America/New_York'  => ['hour', 1, '2020-02-29 16:05:15', 'America/New_York', '2020-02-29 16:00:00'],
			'1h: ceil sub America/New_York'   => ['hour', 1, '2020-02-29 16:59:15', 'America/New_York', '2020-02-29 17:00:00'],
			'4h: no change America/New_York'  => ['hour', 4, '2020-02-29 16:05:15', 'America/New_York', '2020-02-29 16:00:00'],
			'4h: floor America/New_York'      => ['hour', 4, '2020-02-29 17:00:15', 'America/New_York', '2020-02-29 16:00:00'],
			'4h: ceil America/New_York'       => ['hour', 4, '2020-02-29 15:08:15', 'America/New_York', '2020-02-29 16:00:00'],
			'4h: ceil sub America/New_York'   => ['hour', 4, '2020-02-29 14:07:31', 'America/New_York', '2020-02-29 16:00:00'],
			'4h: carry America/New_York'      => ['hour', 4, '2020-02-29 23:53:15', 'America/New_York', '2020-03-01 00:00:00'],
			'1D: no change America/New_York'  => ['day', 1, '2020-02-29 09:05:15', 'America/New_York', '2020-02-29 00:00:00'],
			'1D: ceil sub America/New_York'   => ['day', 1, '2020-02-29 16:05:15', 'America/New_York', '2020-03-01 00:00:00'],
			'1M: no change America/New_York'  => ['month', 1, '2020-02-14 09:05:15', 'America/New_York', '2020-02-01 00:00:00'],
			'1M: ceil sub America/New_York'   => ['month', 1, '2020-02-29 16:05:15', 'America/New_York', '2020-03-01 00:00:00'],
			'1Y: no change America/New_York'  => ['year', 1, '2020-02-14 09:05:15', 'America/New_York', '2020-01-01 00:00:00'],
			'1Y: ceil sub America/New_York'   => ['year', 1, '2020-09-29 16:05:15', 'America/New_York', '2021-01-01 00:00:00'],

			'kirby/issues/3642 America/New_York' => ['minute', 5, '2021-08-18 10:59:00', 'America/New_York', '2021-08-18 11:00:00'],

			'1s: no change Asia/Tokyo'  => ['second', 1, '2020-02-29 16:05:15', 'Asia/Tokyo', '2020-02-29 16:05:15'],
			'5s: no change Asia/Tokyo'  => ['second', 5, '2020-02-29 16:05:15', 'Asia/Tokyo', '2020-02-29 16:05:15'],
			'5s: floor Asia/Tokyo'      => ['second', 5, '2020-02-29 16:05:12', 'Asia/Tokyo', '2020-02-29 16:05:10'],
			'5s: ceil Asia/Tokyo'       => ['second', 5, '2020-02-29 16:05:13', 'Asia/Tokyo', '2020-02-29 16:05:15'],
			'5s: carry Asia/Tokyo'      => ['second', 5, '2020-02-29 16:59:58', 'Asia/Tokyo', '2020-02-29 17:00:00'],
			'1m: no change Asia/Tokyo'  => ['minute', 1, '2020-02-29 16:05:15', 'Asia/Tokyo', '2020-02-29 16:05:00'],
			'1m: ceil sub Asia/Tokyo'   => ['minute', 1, '2020-02-29 16:05:55', 'Asia/Tokyo', '2020-02-29 16:06:00'],
			'15m: no change Asia/Tokyo' => ['minute', 1, '2020-02-29 16:07:15', 'Asia/Tokyo', '2020-02-29 16:07:00'],
			'15m: floor Asia/Tokyo'     => ['minute', 15, '2020-02-29 16:07:15', 'Asia/Tokyo', '2020-02-29 16:00:00'],
			'15m: ceil Asia/Tokyo'      => ['minute', 15, '2020-02-29 16:08:15', 'Asia/Tokyo', '2020-02-29 16:15:00'],
			'15m: ceil sub Asia/Tokyo'  => ['minute', 15, '2020-02-29 16:07:31', 'Asia/Tokyo', '2020-02-29 16:15:00'],
			'15m: carry Asia/Tokyo'     => ['minute', 15, '2020-02-29 23:53:15', 'Asia/Tokyo', '2020-03-01 00:00:00'],
			'1h: no change Asia/Tokyo'  => ['hour', 1, '2020-02-29 16:05:15', 'Asia/Tokyo', '2020-02-29 16:00:00'],
			'1h: ceil sub Asia/Tokyo'   => ['hour', 1, '2020-02-29 16:59:15', 'Asia/Tokyo', '2020-02-29 17:00:00'],
			'4h: no change Asia/Tokyo'  => ['hour', 4, '2020-02-29 16:05:15', 'Asia/Tokyo', '2020-02-29 16:00:00'],
			'4h: floor Asia/Tokyo'      => ['hour', 4, '2020-02-29 17:00:15', 'Asia/Tokyo', '2020-02-29 16:00:00'],
			'4h: ceil Asia/Tokyo'       => ['hour', 4, '2020-02-29 15:08:15', 'Asia/Tokyo', '2020-02-29 16:00:00'],
			'4h: ceil sub Asia/Tokyo'   => ['hour', 4, '2020-02-29 14:07:31', 'Asia/Tokyo', '2020-02-29 16:00:00'],
			'4h: carry Asia/Tokyo'      => ['hour', 4, '2020-02-29 23:53:15', 'Asia/Tokyo', '2020-03-01 00:00:00'],
			'1D: no change Asia/Tokyo'  => ['day', 1, '2020-02-29 09:05:15', 'Asia/Tokyo', '2020-02-29 00:00:00'],
			'1D: ceil sub Asia/Tokyo'   => ['day', 1, '2020-02-29 16:05:15', 'Asia/Tokyo', '2020-03-01 00:00:00'],
			'1M: no change Asia/Tokyo'  => ['month', 1, '2020-02-14 09:05:15', 'Asia/Tokyo', '2020-02-01 00:00:00'],
			'1M: ceil sub Asia/Tokyo'   => ['month', 1, '2020-02-29 16:05:15', 'Asia/Tokyo', '2020-03-01 00:00:00'],
			'1Y: no change Asia/Tokyo'  => ['year', 1, '2020-02-14 09:05:15', 'Asia/Tokyo', '2020-01-01 00:00:00'],
			'1Y: ceil sub Asia/Tokyo'   => ['year', 1, '2020-09-29 16:05:15', 'Asia/Tokyo', '2021-01-01 00:00:00'],

			'kirby/issues/3642 Asia/Tokyo' => ['minute', 5, '2021-08-18 10:59:00', 'Asia/Tokyo', '2021-08-18 11:00:00'],
		];
	}

	/**
	 * @covers ::round
	 * @dataProvider roundUnsupportedSizeProvider
	 */
	public function testRoundUnsupportedSize(string $unit, int $size)
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid rounding size for ' . $unit);

		$date = new Date('2020-01-01');
		$date->round($unit, $size);
	}

	public static function roundUnsupportedSizeProvider(): array
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
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid rounding unit');

		$date = new Date('2020-01-01');
		$date->round('foo', 1);
	}

	/**
	 * @covers ::roundedTimestamp
	 */
	public function testRoundedTimestamp()
	{
		$result = Date::roundedTimestamp('2021-12-12 12:12:12');
		$this->assertSame('2021-12-12 12:12:12', date('Y-m-d H:i:s', $result));
	}

	/**
	 * @covers ::roundedTimestamp
	 */
	public function testRoundedTimestampWithStep()
	{
		$result = Date::roundedTimestamp('2021-12-12 12:12:12', [
			'unit' => 'minute',
			'size' => 5
		]);

		$this->assertSame('2021-12-12 12:10:00', date('Y-m-d H:i:s', $result));
	}

	/**
	 * @covers ::roundedTimestamp
	 */
	public function testRoundedTimestampWithInvalidDate()
	{
		$result = Date::roundedTimestamp('invalid date');
		$this->assertNull($result);
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
		$this->expectException(InvalidArgumentException::class);
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
		$this->expectException(InvalidArgumentException::class);
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
