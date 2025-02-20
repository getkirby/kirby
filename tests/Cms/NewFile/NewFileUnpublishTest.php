<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use Kirby\Cms\NewPage as Page;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileUnpublishTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileUnpublish';

	public function testUnpublish(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
		]);

		// create an empty dummy file
		F::write($file->root(), '');

		$this->assertFileDoesNotExist($file->mediaRoot());
		$file->publish();
		$this->assertFileExists($file->mediaRoot());
		$file->unpublish();
		$this->assertFileDoesNotExist($file->mediaRoot());
	}
}
