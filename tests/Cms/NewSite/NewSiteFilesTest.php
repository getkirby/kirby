<?php

namespace Kirby\Cms;

use Kirby\Cms\NewSite as Site;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Site::class)]
class NewSiteFilesTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewSiteFiles';

	public function testCreateFile(): void
	{
		F::write($source = static::TMP . '/source.md', '');

		$site = new Site();
		$file = $site->createFile([
			'filename' => 'test.md',
			'source'   => $source
		]);

		$this->assertSame('test.md', $file->filename());
	}

	public function testFiles(): void
	{
		$site  = new Site([
			'files' => [
				['filename' => 'test.md']
			]
		]);

		$this->assertInstanceOf(Files::class, $site->files());
		$this->assertCount(1, $site->files());
		$this->assertSame('test.md', $site->files()->first()->filename());
	}

	public function testFilesDefault(): void
	{
		$site = new Site();
		$this->assertInstanceOf(Files::class, $site->files());
		$this->assertCount(0, $site->files());
	}

	public function testFilesInvalid(): void
	{
		$this->expectException(TypeError::class);
		new Site(['files' => 'files']);
	}
}
