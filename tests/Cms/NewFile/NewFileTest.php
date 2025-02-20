<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use Kirby\Cms\NewPage as Page;
use Kirby\Filesystem\File as BaseFile;
use Kirby\Panel\File as PanelFile;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFile';

	public function testApiUrl(): void
	{
		$this->app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		// site file
		$file = new File([
			'filename' => 'site-file.jpg',
			'parent'   => $this->app->site()
		]);
		$this->assertSame('https://getkirby.com/api/site/files/site-file.jpg', $file->apiUrl());
		$this->assertSame('site/files/site-file.jpg', $file->apiUrl(true));

		// page file
		$mother = new Page(['slug' => 'mother']);
		$child  = new Page([
			'slug'   => 'child',
			'parent' => $mother
		]);

		$file = new File([
			'filename' => 'page-file.jpg',
			'parent'   => $child
		]);

		$this->assertSame('https://getkirby.com/api/pages/mother+child/files/page-file.jpg', $file->apiUrl());
		$this->assertSame('pages/mother+child/files/page-file.jpg', $file->apiUrl(true));

		// user file
		$user = new User(['id' => 'test']);
		$file = new File([
			'filename' => 'user-file.jpg',
			'parent'   => $user
		]);

		$this->assertSame('https://getkirby.com/api/users/test/files/user-file.jpg', $file->apiUrl());
		$this->assertSame('users/test/files/user-file.jpg', $file->apiUrl(true));
	}

	public function testAsset(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'url'      => 'https://getkirby.com/projects/project-a/cover.jpg'
		]);

		$this->assertInstanceOf(BaseFile::class, $file->asset());
		$this->assertSame(
			'https://getkirby.com/projects/project-a/cover.jpg',
			$file->asset()->url()
		);
	}

	public function testFilename(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
		]);

		$this->assertSame('test.jpg', $file->filename());
	}

	public function testPage(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$this->assertIsPage($page, $file->page());

		$user = new User([]);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $user
		]);

		$this->assertNull($file->page());
	}

	public function testParentId(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$this->assertSame('test', $file->parentId());
	}

	public function testToString(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$this->assertSame('test.jpg', $file->toString('{{ file.filename }}'));
	}

	public function testPanel(): void
	{
		$page = new Page(['slug'  => 'test']);
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page
		]);

		$this->assertInstanceOf(PanelFile::class, $file->panel());
	}

	public function testQuery(): void
	{
		$page = new Page(['slug'  => 'test']);
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page
		]);

		$this->assertSame('test.pdf', $file->query('file.filename'));
		$this->assertSame('test.pdf', $file->query('model.filename'));
	}
}
