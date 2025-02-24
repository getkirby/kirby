<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FilePublishTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FilePublish';

	public function testPublish(): void
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
	}
}
