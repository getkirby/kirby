<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileUrlTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileUrl';

	public function testPermalink(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.pdf',
			'content'  => ['uuid' => 'my-file-uuid'],
			'parent'   => $page
		]);

		$this->assertSame('//@/file/my-file-uuid', $file->permalink());
	}

	public function testUrl(): void
	{
		$file = new File([
			'filename' => 'test.pdf',
			'url'      => $url = 'http://getkirby.com/test.pdf',
			'parent'   => $this->app->site()
		]);

		$this->assertSame($url, $file->url());
	}
}
