<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileMediaTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileMedia';

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

	public function testMediaHash(): void
	{
		F::write($root = static::TMP . '/content/test.jpg', 'test');
		touch($root, 5432112345);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertSame('08756f3115-5432112345', $file->mediaHash());
	}

	public function testMediaPath(): void
	{
		F::write($root = static::TMP . '/content/test.jpg', 'test');
		touch($root, 5432112345);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertSame(self::TMP . '/media/site/08756f3115-5432112345/test.jpg', $file->mediaPath());
		$this->assertSame(self::TMP . '/media/site/08756f3115-5432112345/test-120x.jpg', $file->mediaPath('test-120x.jpg'));
	}

	public function testMediaRoot(): void
	{
		F::write($root = static::TMP . '/content/test.jpg', 'test');
		touch($root, 5432112345);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertSame(self::TMP . '/media/site/08756f3115-5432112345', $file->mediaRoot());
	}

	public function testMediaToken(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertSame('08756f3115', $file->mediaToken());
	}

	public function testMediaUrl(): void
	{
		F::write($root = static::TMP . '/content/test.jpg', 'test');
		touch($root, 5432112345);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertSame('/media/site/08756f3115-5432112345/test.jpg', $file->mediaUrl());
		$this->assertSame('/media/site/08756f3115-5432112345/test-120x.jpg', $file->mediaUrl('test-120x.jpg'));
	}
}
