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
	public const string TMP = KIRBY_TMP_DIR . '/Cms.Files';

	public function testLazyHydration(): void
	{
		$parent = new Page(['slug' => 'test']);

		$files = Files::factory([
			['filename' => 'a.jpg'],
			['filename' => 'b.jpg'],
			['filename' => 'c.jpg']
		], $parent);

		// the structure is known without creating any file object
		$this->assertSame(3, $files->count());
		$this->assertSame(['test/a.jpg', 'test/b.jpg', 'test/c.jpg'], $files->keys());
		$this->assertTrue($files->has('test/b.jpg'));
		$this->assertSame([null, null, null], array_values($files->data));

		// a single lookup only creates the requested file
		$file = $files->find('b.jpg');
		$this->assertSame('b.jpg', $file->filename());
		$this->assertNull($files->data['test/a.jpg']);

		// the created file is cached, so every following
		// access returns the very same object
		$this->assertSame($file, $files->find('b.jpg'));
		$this->assertSame($file, $files->nth(1));
	}

	public function testLazyHydrationSharesObjectsWithClones(): void
	{
		$parent = new Page(['slug' => 'test']);

		$files = Files::factory([
			['filename' => 'a.jpg'],
			['filename' => 'b.jpg'],
			['filename' => 'c.jpg']
		], $parent);

		// a derived collection must not create its own file objects,
		// otherwise changes to a file would not be visible everywhere
		$this->assertSame($files->nth(1), $files->slice(1)->first());
		$this->assertSame($files->last(), $files->flip()->first());
	}

	public function testLazyHydrationWithDifferentParents(): void
	{
		$a = Files::factory([['filename' => 'a.jpg']], new Page(['slug' => 'one']));
		$b = Files::factory([['filename' => 'b.jpg']], new Page(['slug' => 'two']));

		// the parent is added during hydration, so files of another
		// parent have to be created before they can be merged
		$merged = $a->add($b);

		$this->assertSame(['one/a.jpg', 'two/b.jpg'], $merged->keys());

		// the merged file must keep its own parent, not inherit
		// the one of the collection it was merged into
		$this->assertSame('two', $merged->nth(1)->parent()->slug());
		$this->assertSame('one', $merged->nth(0)->parent()->slug());
	}

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

	public function testDeleteWithUuids(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'files' => [
					['filename' => 'a.jpg', 'content' => ['uuid' => 'test-a']],
					['filename' => 'b.jpg', 'content' => ['uuid' => 'test-b']]
				]
			]
		]);

		$app->impersonate('kirby');

		$files = $app->site()->files();

		$a = $files->get('a.jpg')->root();
		$b = $files->get('b.jpg')->root();

		// pretend the files exist
		F::write($a, '');
		F::write($b, '');

		// the Panel sends the selection as UUIDs
		$files->delete([
			'file://test-a',
			'file://test-b',
		]);

		$this->assertCount(0, $files);
		$this->assertFileDoesNotExist($a);
		$this->assertFileDoesNotExist($b);

		Uuids::cache()->flush();
	}

	public function testDeleteWithForeignUuid(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							['filename' => 'a.jpg', 'content' => ['uuid' => 'test-a']]
						]
					],
					[
						'slug'  => 'b',
						'files' => [
							['filename' => 'b.jpg', 'content' => ['uuid' => 'test-b']]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$files = $app->page('a')->files();
		$b     = $app->page('b')->file('b.jpg')->root();
		F::write($b, '');

		// deleting a file that is not part of the collection must fail,
		// even if the UUID resolves to a valid file elsewhere
		try {
			$files->delete(['file://test-b']);
		} catch (Exception $e) {
			$this->assertSame('Not all files could be deleted. Try each remaining file individually to see the specific error that prevents deletion.', $e->getMessage());
		}

		$this->assertFileExists($b);

		Uuids::cache()->flush();
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
