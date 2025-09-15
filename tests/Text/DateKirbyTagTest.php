<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\TestCase;

class DateKirbyTagTest extends TestCase
{
	public function testDate(): void
	{
		$app = App::instance();
		$this->assertSame(date('d.m.Y'), $app->kirbytags('(date: d.m.Y)'));
		$this->assertSame(date('Y'), $app->kirbytags('(date: year)'));
	}
}
