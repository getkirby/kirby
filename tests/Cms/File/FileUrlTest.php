<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileUrlTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileUrl';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'roots' => [
				'index' => self::TMP
			],
			'options' => [
				'content.salt' => 'test'
			]
		]);
	}

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

	public function testUrlFixed(): void
	{
		$file = new File([
			'filename' => 'test.pdf',
			'url'      => $url = 'http://getkirby.com/test.pdf',
			'parent'   => $this->app->site()
		]);

		$this->assertSame($url, $file->url());
	}

	public function testUrlMedia(): void
	{
		F::copy(self::FIXTURES . '/test.pdf', $root = self::TMP . '/content/test.pdf');
		touch($root, 1234567890);

		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $this->app->site()
		]);

		$this->assertSame('/media/site/b22a2d4f82-1234567890/test.pdf', $file->url());
	}
}
