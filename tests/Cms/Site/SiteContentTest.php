<?php

namespace Kirby\Cms;

use Kirby\Content\Content;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Site::class)]
class SiteContentTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.SiteContent';

	public function testContent(): void
	{
		$content = [
			'title' => 'Maegazine',
			'text'  => 'lorem ipsum'
		];

		$site = new Site([
			'content' => $content
		]);

		$this->assertInstanceOf(Content::class, $site->content());
		$this->assertSame($content, $site->content()->toArray());
		$this->assertSame('lorem ipsum', $site->text()->value());
	}

	public function testInvalidContent(): void
	{
		$this->expectException(TypeError::class);
		new Site(['content' => 'content']);
	}
}
