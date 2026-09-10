<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\App;
use Kirby\TestCase;
use Kirby\Text\KirbyTag;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TelTag::class)]
class TelTagTest extends TestCase
{
	protected function setUp(): void
	{
		KirbyTag::$types = ['tel' => TelTag::class];
	}

	protected function tearDown(): void
	{
		KirbyTag::$aliases = [];
		KirbyTag::$types   = [];
		App::destroy();
	}

	public function testRender(): void
	{
		$tag = TelTag::factory('tel', '+49123456789');
		$this->assertSame(
			'<a href="tel:+49123456789">+49123456789</a>',
			$tag->render()
		);
	}

	public function testRenderWithAttrs(): void
	{
		$tag = TelTag::factory('tel', '+49123456789', [
			'class' => 'phone',
			'text'  => 'Call us'
		]);
		$this->assertSame(
			'<a class="phone" href="tel:+49123456789">Call us</a>',
			$tag->render()
		);
	}
}
