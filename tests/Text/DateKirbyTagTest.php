<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class DateKirbyTagTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Text.DateKirbyTag';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testDate(): void
	{
		$this->assertSame(date('d.m.Y'), $this->app->kirbytags('(date: d.m.Y)'));
		$this->assertSame(date('Y'), $this->app->kirbytags('(date: year)'));
	}

	public function testDateWithHtml(): void
	{
		// HTML special characters in the format must be escaped
		$this->assertSame('&lt;b&gt;', $this->app->kirbytags('(date: <b>)'));
	}

	public function testDateYearSetsCacheExpiry(): void
	{
		$this->assertSame(date('Y'), $this->app->kirbytags('(date: year)'));
		$this->assertSame(
			strtotime('first day of January next year'),
			$this->app->response()->expires()
		);
	}

	public function testDateYearWithCustomExpiry(): void
	{
		$this->assertSame(date('Y'), $this->app->kirbytags('(date: year expiry: tomorrow)'));
		$this->assertSame(strtotime('tomorrow'), $this->app->response()->expires());
	}

	public function testDateWithoutExpiry(): void
	{
		$this->assertSame(date('d.m.Y'), $this->app->kirbytags('(date: d.m.Y)'));
		$this->assertNull($this->app->response()->expires());
	}

	public function testDateWithCustomExpiry(): void
	{
		$this->assertSame(date('d.m.Y'), $this->app->kirbytags('(date: d.m.Y expiry: tomorrow)'));
		$this->assertSame(strtotime('tomorrow'), $this->app->response()->expires());
	}
}
