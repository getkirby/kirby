<?php

namespace Kirby\Api;

use Kirby\Cms\File;
use Kirby\Cms\FileVersion;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Filesystem\Dir;

class FileVersionModelTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Api.FileVersionModel';

	protected File $file;
	protected Site|Page|User $parent;

	public function setUp(): void
	{
		parent::setUp();

		$this->parent = new Page([
			'root' => static::TMP,
			'slug' => 'test'
		]);

		$this->file = new File([
			'filename' => 'test.jpg',
			'parent' => $this->parent,
			'content' => [
				'title' => 'Test Title'
			]
		]);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testExists(): void
	{
		$version = new FileVersion([
			'original' => $this->file,
			'root'     => static::TMP . '/test-version.jpg',
		]);

		$this->assertAttr($version, 'exists', false);
	}

	public function testType(): void
	{
		$version = new FileVersion([
			'original' => $this->file,
			'root'     => static::TMP . '/test-version.jpg',
		]);

		$this->assertAttr($version, 'type', 'image');
	}

	public function testUrl(): void
	{
		$version = new FileVersion([
			'original' => $this->file,
			'root'     => static::TMP . '/test-version.jpg',
		]);

		$this->assertAttr($version, 'url', null);
	}
}
