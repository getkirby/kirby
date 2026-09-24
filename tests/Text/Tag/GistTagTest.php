<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\App;
use Kirby\TestCase;
use Kirby\Text\KirbyTag;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GistTag::class)]
class GistTagTest extends TestCase
{
	protected function setUp(): void
	{
		KirbyTag::$types = ['gist' => GistTag::class];
	}

	protected function tearDown(): void
	{
		KirbyTag::$aliases = [];
		KirbyTag::$types   = [];
		App::destroy();
	}

	public function testRender(): void
	{
		$tag = GistTag::factory('gist', 'https://gist.github.com/bastianallgeier/deae448c5913d79809a6');
		$this->assertSame(
			'<script src="https://gist.github.com/bastianallgeier/deae448c5913d79809a6.js"></script>',
			$tag->render()
		);
	}

	public function testRenderWithFile(): void
	{
		$tag = GistTag::factory('gist', 'https://gist.github.com/bastianallgeier/deae448c5913d79809a6', [
			'file' => 'test.php'
		]);
		$this->assertSame(
			'<script src="https://gist.github.com/bastianallgeier/deae448c5913d79809a6.js?file=test.php"></script>',
			$tag->render()
		);
	}
}
