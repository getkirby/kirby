<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Files::class)]
class FilesMethodsTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FilesMethodsTest';

	public function testFilesMethod(): void
	{
		$this->app = $this->app->clone([
			'filesMethods' => [
				'test' => fn () => 'files method'
			]
		]);

		$files = new Files([]);
		$this->assertSame('files method', $files->test());
	}
}
