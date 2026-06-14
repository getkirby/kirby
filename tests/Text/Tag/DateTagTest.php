<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\App;
use Kirby\TestCase;
use Kirby\Text\KirbyTag;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DateTag::class)]
class DateTagTest extends TestCase
{
	protected function setUp(): void
	{
		KirbyTag::$types = ['date' => DateTag::class];
	}

	protected function tearDown(): void
	{
		KirbyTag::$aliases = [];
		KirbyTag::$types   = [];
		App::destroy();
	}

	public function testAttrs(): void
	{
		$this->assertSame(['expiry'], DateTag::attrs());
	}

	public function testRender(): void
	{
		$tag = DateTag::factory('date', 'd.m.Y');
		$this->assertSame(date('d.m.Y'), $tag->render());
	}

	public function testRenderEscapesValue(): void
	{
		// special characters in the value must not inject HTML
		$tag = DateTag::factory('date', '<b>');
		$this->assertSame('&lt;b&gt;', $tag->render());
	}

	public function testRenderYear(): void
	{
		$tag = DateTag::factory('date', 'year');
		$this->assertSame(date('Y'), $tag->render());

		// keyword is case-insensitive
		$tag = DateTag::factory('date', 'YEAR');
		$this->assertSame(date('Y'), $tag->render());
	}
}
