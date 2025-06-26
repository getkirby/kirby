<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Uuid\Uuids;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Files::class)]
class FilesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Files';

	public function testAddFile(): void
	{
		$parent = new Page(['slug' => 'test']);

		$files = Files::factory([
			['filename' => 'a.jpg']
		], $parent);

		$file = new File([
			'filename' => 'b.jpg',
			'parent'   => $parent
		]);

		$result = $files->add($file);

		$this->assertCount(2, $result);
		$this->assertSame('a.jpg', $result->nth(0)->filename());
		$this->assertSame('b.jpg', $result->nth(1)->filename());
	}

	public function testAddCollection(): void
	{
		$parent = new Page(['slug' => 'test']);

		$a = Files::factory([
			['filename' => 'a.jpg']
		], $parent);

		$b = Files::factory([
			['filename' => 'b.jpg'],
			['filename' => 'c.jpg']
		], $parent);

		$c = $a->add($b);

		$this->assertCount(3, $c);
		$this->assertSame('a.jpg', $c->nth(0)->filename());
		$this->assertSame('b.jpg', $c->nth(1)->filename());
		$this->assertSame('c.jpg', $c->nth(2)->filename());
	}

	public function testAddById(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'a.jpg'],
							['filename' => 'b.jpg'],
						]
					],
					[
						'slug' => 'b',
						'files' => [
							['filename' => 'a.jpg'],
						]
					]
				]
			]
		]);

		$files = $app->page('a')->files()->add('b/a.jpg');

		$this->assertCount(3, $files);
		$this->assertSame('a/a.jpg', $files->nth(0)->id());
		$this->assertSame('a/b.jpg', $files->nth(1)->id());
		$this->assertSame('b/a.jpg', $files->nth(2)->id());
	}

	public function testAddNull(): void
	{
		$files = new Files();
		$this->assertCount(0, $files);

		$files->add(null);

		$this->assertCount(0, $files);
	}

	public function testAddFalse(): void
	{
		$files = new Files();
		$this->assertCount(0, $files);

		$files->add(false);

		$this->assertCount(0, $files);
	}

	public function testAddInvalidObject(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('You must pass a Files or File object or an ID of an existing file to the Files collection');

		$site  = new Site();
		$files = new Files();
		$files->add($site);
	}

	public function testDelete(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'files' => [
					['filename' => 'b.jpg'],
					['filename' => 'a.jpg']
				]
			]
		]);

		$app->impersonate('kirby');

		$files = $app->site()->files();

		$this->assertCount(2, $files);

		$a = $files->get('a.jpg')->root();
		$b = $files->get('b.jpg')->root();

		// pretend the files exist
		F::write($a, '');
		F::write($b, '');

		$this->assertFileExists($a);
		$this->assertFileExists($b);

		$files->delete([
			'a.jpg',
			'b.jpg',
		]);

		$this->assertCount(0, $files);

		$this->assertFileDoesNotExist($a);
		$this->assertFileDoesNotExist($b);
	}

	public function testDeleteWithInvalidIds(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'files' => [
					['filename' => 'b.jpg'],
					['filename' => 'a.jpg']
				]
			]
		]);

		$app->impersonate('kirby');

		$files = $app->site()->files();

		$this->assertCount(2, $files);

		$a = $files->get('a.jpg')->root();
		$b = $files->get('b.jpg')->root();

		// pretend the files exist
		F::write($a, '');
		F::write($b, '');

		$this->assertFileExists($a);
		$this->assertFileExists($b);

		try {
			$files->delete([
				'a.jpg',
				'c.jpg',
			]);
		} catch (Exception $e) {
			$this->assertSame('Not all files could be deleted. Try each remaining file individually to see the specific error that prevents deletion.', $e->getMessage());
		}

		$this->assertCount(1, $files);
		$this->assertSame('b.jpg', $files->first()->filename());

		$this->assertFileDoesNotExist($a);
		$this->assertFileExists($b);
	}

	public function testFindByUuid(): void
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => $a = 'a.jpg',
						'content' => ['uuid' => 'test-a']
					],
					[
						'filename' => $b = 'b.jpg',
						'content' => ['uuid' => 'test-b']
					]
				]
			]
		]);

		$files = $app->site()->files();
		$this->assertSame($a, $files->find('file://test-a')->filename());
		$this->assertSame($b, $files->find('file://test-b')->filename());

		$this->assertSame($a, $app->file('file://test-a')->filename());
		$this->assertSame($b, $app->file('file://test-b')->filename());

		Uuids::cache()->flush();
	}

	public function testSize(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		F::write($a = static::TMP . '/content/test/a.txt', 'foo');
		F::write($b = static::TMP . '/content/test/b.txt', 'bar');

		$files = Files::factory([
			['filename' => 'a.txt', 'root' => $a],
			['filename' => 'b.txt', 'root' => $b]
		], $app->page('test'));


		$this->assertSame(6, $files->size());
		$this->assertSame('6 B', $files->niceSize());
	}

	public function testSortedByFilename(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'files' => [
					['filename' => 'b.jpg'],
					['filename' => 'a.jpg']
				]
			]
		]);

		$files = $app->site()->files()->sorted();

		$this->assertSame('a.jpg', $files->first()->filename());
		$this->assertSame('b.jpg', $files->last()->filename());
	}

	public function testSortedBySort(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'files' => [
					['filename' => 'a.jpg', 'content' => ['sort' => 2]],
					['filename' => 'b.jpg', 'content' => ['sort' => 1]]
				]
			]
		]);

		$files = $app->site()->files()->sorted();

		$this->assertSame('b.jpg', $files->first()->filename());
		$this->assertSame('a.jpg', $files->last()->filename());
	}
}
