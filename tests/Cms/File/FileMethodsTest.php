<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileMethodsTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileMethods';

	public function testFileMethod(): void
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
