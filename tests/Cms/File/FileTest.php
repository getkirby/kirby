<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as FilesystemFile;
use Kirby\Http\Response;
use Kirby\Panel\File as PanelFile;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.File';

	protected function file(): File
	{
		F::write(static::TMP . '/content/test/test.txt', 'test');

		return new File([
			'filename' => 'test.txt',
			'parent'   => new Page(['slug' => 'test'])
		]);
	}

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

		$this->assertInstanceOf(FilesystemFile::class, $file->asset());
		$this->assertSame(
			'https://getkirby.com/projects/project-a/cover.jpg',
			$file->asset()->url()
		);
	}

	public function testBase64(): void
	{
		$this->assertSame('dGVzdA==', $this->file()->base64());
	}

	public function testConstructWithoutFilename(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('The filename and parent are required');

		new File([]);
	}

	public function testConstructWithoutParent(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('The filename and parent are required');

		new File(['filename' => 'test.jpg']);
	}

	public function testDataUri(): void
	{
		$file = $this->file();
		$this->assertSame('data:text/plain;base64,dGVzdA==', $file->dataUri());
		$this->assertSame('data:text/plain,test', $file->dataUri(false));
	}

	public function testDownload(): void
	{
		$this->assertSame('test', $this->file()->download());
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

	public function testHash(): void
	{
		$file = $this->file();
		$this->assertSame(md5($file->root()), $file->hash());
	}

	public function testHeader(): void
	{
		$file = $this->file();
		$this->assertNull($file->header());

		$response = $file->header(false);
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame('text/plain', $response->type());
	}

	public function testIsWritable(): void
	{
		$this->assertTrue($this->file()->isWritable());
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

	public function testPanel(): void
	{
		$page = new Page(['slug'  => 'test']);
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page
		]);

		$this->assertInstanceOf(PanelFile::class, $file->panel());
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

	public function testParentIdWithSite(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => new Site()
		]);

		$this->assertNull($file->parentId());
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

	public function testRead(): void
	{
		$this->assertSame('test', $this->file()->read());
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
}
