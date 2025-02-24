<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileSiblingsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileSiblings';

	protected function collection(): array
	{
		return [
			['filename' => 'cover.jpg',     'template' => 'cover'],
			['filename' => 'gallery-1.jpg', 'template' => 'gallery'],
			['filename' => 'gallery-2.jpg', 'template' => 'gallery'],
			['filename' => 'gallery-3.jpg', 'template' => 'gallery']
		];
	}

	protected function files(): Files
	{
		return (new Page([
			'slug'  => 'test',
			'files' => $this->collection(),
		]))->files();
	}

	public function testHasNext(): void
	{
		$collection = $this->files();

		$this->assertTrue($collection->first()->hasNext());
		$this->assertFalse($collection->last()->hasNext());
	}

	public function testHasPrev(): void
	{
		$collection = $this->files();

		$this->assertTrue($collection->last()->hasPrev());
		$this->assertFalse($collection->first()->hasPrev());
	}

	public function testIndexOf(): void
	{
		$collection = $this->files();

		$this->assertSame(0, $collection->first()->indexOf());
		$this->assertSame(1, $collection->nth(1)->indexOf());
		$this->assertSame(3, $collection->last()->indexOf());
	}

	public function testIsFirst(): void
	{
		$collection = $this->files();

		$this->assertTrue($collection->first()->isFirst());
		$this->assertFalse($collection->last()->isFirst());
	}

	public function testIsLast(): void
	{
		$collection = $this->files();

		$this->assertTrue($collection->last()->isLast());
		$this->assertFalse($collection->first()->isLast());
	}

	public function testIsNth(): void
	{
		$collection = $this->files();

		$this->assertTrue($collection->first()->isNth(0));
		$this->assertTrue($collection->nth(1)->isNth(1));
		$this->assertTrue($collection->last()->isNth($collection->count() - 1));
	}

	public function testNext(): void
	{
		$collection = $this->files();

		$this->assertSame($collection->first()->next(), $collection->nth(1));
	}

	public function testNextAll(): void
	{
		$collection = $this->files();
		$first      = $collection->first();

		$this->assertCount(3, $first->nextAll());

		$this->assertSame($first->nextAll()->first(), $collection->nth(1));
		$this->assertSame($first->nextAll()->last(), $collection->nth(3));
	}

	public function testPrev(): void
	{
		$collection = $this->files();

		$this->assertSame($collection->last()->prev(), $collection->nth(2));
	}

	public function testPrevAll(): void
	{
		$collection = $this->files();
		$last       = $collection->last();

		$this->assertCount(3, $last->prevAll());

		$this->assertSame($last->prevAll()->first(), $collection->nth(0));
		$this->assertSame($last->prevAll()->last(), $collection->nth(2));
	}

	public function testSiblings(): void
	{
		$files    = $this->files();
		$file     = $files->nth(1);
		$siblings = $files->not($file);

		$this->assertSame($files, $file->siblings());
		$this->assertEquals($siblings, $file->siblings(false)); // cannot use strict assertion (cloned object)
	}

	public function testTemplateSiblings(): void
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'a.jpg',
					'template' => 'gallery'
				],
				[
					'filename' => 'b.jpg',
					'template' => 'cover'
				],
				[
					'filename' => 'c.jpg',
					'template' => 'gallery'
				],
				[
					'filename' => 'd.jpg',
					'template' => 'gallery'
				]
			]
		]);

		$files    = $page->files();
		$siblings = $files->first()->templateSiblings();

		$this->assertTrue($siblings->has('test/a.jpg'));
		$this->assertTrue($siblings->has('test/c.jpg'));
		$this->assertTrue($siblings->has('test/d.jpg'));

		$this->assertFalse($siblings->has('test/b.jpg'));

		$siblings = $files->first()->templateSiblings(false);

		$this->assertTrue($siblings->has('test/c.jpg'));
		$this->assertTrue($siblings->has('test/d.jpg'));

		$this->assertFalse($siblings->has('test/a.jpg'));
		$this->assertFalse($siblings->has('test/b.jpg'));
	}
}
