<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileMethodsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileMethods';

	public function testFileMethod()
	{
		$this->app = $this->app->clone([
			'fileMethods' => [
				'test' => fn () => 'file method for: ' . $this->filename()
			]
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertSame('file method for: test.jpg', $file->test());
	}
}
