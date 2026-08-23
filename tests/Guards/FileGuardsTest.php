<?php

namespace Kirby\Guards;

use Kirby\Cms\File;
use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as Upload;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileGuards::class)]
class FileGuardsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.FileGuards';
	public const FIXTURES = __DIR__ . '/../Cms/File/fixtures/files';

	protected function file(string $filename): File
	{
		return new File([
			'filename' => $filename,
			'parent'   => new Page(['slug' => 'test'])
		]);
	}

	protected function fileOnDisk(string $filename): File
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'test']]
			]
		]);

		$this->app->impersonate('kirby');

		$page = $this->app->page('test');

		F::copy(
			source: static::FIXTURES . '/' . $filename,
			target: $page->root() . '/' . $filename
		);

		return $page->file($filename);
	}

	protected function guards(File $file): FileGuards
	{
		return new FileGuards(
			model: $file,
			user: $this->user()
		);
	}

	protected function user(): User
	{
		return $this->app->user() ?? new User(['id' => 'test']);
	}

	public function testChangeSort(): void
	{
		$this->assertNull(
			$this->guards($this->file('test.jpg'))->ensureExecutable('changeSort', 1)
		);
	}

	public function testChangeSortWithoutPermission(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'editor', 'permissions' => ['files' => ['sort' => false]]]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.file.sort.permission');

		$this->guards($this->file('test.jpg'))->ensureExecutable('changeSort', 1);
	}

	public function testCreate(): void
	{
		$file   = $this->file('test.jpg');
		$upload = new Upload(static::FIXTURES . '/test.jpg');

		$this->assertNull($this->guards($file)->ensureExecutable('create', $upload));
	}

	public function testCreateWithDuplicate(): void
	{
		$file   = $this->fileOnDisk('test.jpg');
		$upload = new Upload(static::FIXTURES . '/cat.jpg');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.file.duplicate');

		$this->guards($file)->ensureExecutable('create', $upload);
	}

	public function testCreateWithSameFile(): void
	{
		// uploading the exact same file again changes nothing
		// and therefore needs no checks at all
		$file   = $this->fileOnDisk('test.jpg');
		$upload = new Upload(static::FIXTURES . '/test.jpg');

		$this->assertTrue($file->isIdentical($upload));
		$this->assertNull($this->guards($file)->ensureExecutable('create', $upload));
	}
}
