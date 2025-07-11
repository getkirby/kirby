<?php

namespace Kirby\Toolkit;

use Kirby\Exception\ErrorPageException;
use Kirby\Exception\Exception;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PaginationTest extends TestCase
{
	public function setUp(): void
	{
		Pagination::$validate = true;
	}

	public function testDefaultPage(): void
	{
		$pagination = new Pagination();
		$this->assertSame(0, $pagination->page());

		$pagination = new Pagination(['total' => 1]);
		$this->assertSame(1, $pagination->page());
	}

	public function testPage(): void
	{
		$pagination = new Pagination(['total' => 100, 'page' => 2]);
		$this->assertSame(2, $pagination->page());
	}

	public function testPageString(): void
	{
		$pagination = new Pagination(['total' => 100, 'page' => '2']);
		$this->assertSame(2, $pagination->page());
	}

	public function testPageEmptyCollection(): void
	{
		$pagination = new Pagination(['total' => 0, 'page' => 1]);
		$this->assertSame(0, $pagination->page());
	}

	public function testTotalDefault(): void
	{
		$pagination = new Pagination();
		$this->assertSame(0, $pagination->total());
	}

	public function testTotal(): void
	{
		$pagination = new Pagination(['total' => 12]);
		$this->assertSame(12, $pagination->total());
	}

	public function testLimitDefault(): void
	{
		$pagination = new Pagination();
		$this->assertSame(20, $pagination->limit());
	}

	public function testLimit(): void
	{
		$pagination = new Pagination(['limit' => 100]);
		$this->assertSame(100, $pagination->limit());
	}

	public function testStart(): void
	{
		$pagination = new Pagination([
			'total' => 42
		]);

		$this->assertSame(1, $pagination->start());

		// go to the second page
		$pagination = $pagination->clone(['page' => 2]);
		$this->assertSame(21, $pagination->start());

		// set a different limit
		$pagination = $pagination->clone(['limit' => 10]);
		$this->assertSame(11, $pagination->start());
	}

	public function testEnd(): void
	{
		$pagination = new Pagination([
			'total' => 42
		]);

		$this->assertSame(20, $pagination->end());

		// go to the second page
		$pagination = $pagination->clone(['page' => 2]);
		$this->assertSame(40, $pagination->end());

		// set a different limit
		$pagination = $pagination->clone(['limit' => 10]);
		$this->assertSame(20, $pagination->end());
	}

	public function testEndWithOneItem(): void
	{
		$pagination = new Pagination([
			'total' => 1
		]);

		$this->assertSame(1, $pagination->end());
	}

	public function testPages(): void
	{
		$pagination = new Pagination();
		$this->assertSame(0, $pagination->pages());

		$pagination = new Pagination(['total' => 1]);
		$this->assertSame(1, $pagination->pages());

		$pagination = new Pagination(['total' => 10]);
		$this->assertSame(1, $pagination->pages());

		$pagination = new Pagination(['total' => 21]);
		$this->assertSame(2, $pagination->pages());

		$pagination = new Pagination(['total' => 11, 'limit' => 5]);
		$this->assertSame(3, $pagination->pages());
	}

	public function testFirstPage(): void
	{
		$pagination = new Pagination();
		$this->assertSame(0, $pagination->firstPage());

		$pagination = new Pagination(['total' => 1]);
		$this->assertSame(1, $pagination->firstPage());

		$pagination = new Pagination(['total' => 42]);
		$this->assertSame(1, $pagination->firstPage());
	}

	public function testLastPage(): void
	{
		$pagination = new Pagination();
		$this->assertSame(0, $pagination->lastPage());

		$pagination = new Pagination(['total' => 1]);
		$this->assertSame(1, $pagination->lastPage());

		$pagination = new Pagination(['total' => 42]);
		$this->assertSame(3, $pagination->lastPage());
	}

	public function testOffset(): void
	{
		$pagination = new Pagination();
		$this->assertSame(0, $pagination->offset());

		$pagination = new Pagination(['total' => 42, 'page' => 2]);
		$this->assertSame(20, $pagination->offset());

		$pagination = new Pagination(['total' => 42, 'page' => 2, 'limit' => 10]);
		$this->assertSame(10, $pagination->offset());
	}

	public function testHasPage(): void
	{
		$pagination = new Pagination([
			'page'  => 1,
			'limit' => 1,
			'total' => 10
		]);

		$this->assertTrue($pagination->hasPage(1));
		$this->assertTrue($pagination->hasPage(10));

		$this->assertFalse($pagination->hasPage(0));
		$this->assertFalse($pagination->hasPage(11));
	}

	public function testHasPages(): void
	{
		$pagination = new Pagination();
		$this->assertFalse($pagination->hasPages());

		$pagination = new Pagination(['total' => 1]);
		$this->assertFalse($pagination->hasPages());

		$pagination = new Pagination(['total' => 21]);
		$this->assertTrue($pagination->hasPages());
	}

	public function testHasPrevPage(): void
	{
		$pagination = new Pagination();
		$this->assertFalse($pagination->hasPrevPage());

		$pagination = new Pagination(['total' => 42, 'page' => 2]);
		$this->assertTrue($pagination->hasPrevPage());
	}

	public function testPrevPage(): void
	{
		$pagination = new Pagination(['page' => 2, 'total' => 42]);
		$this->assertSame(1, $pagination->prevPage());

		$pagination = new Pagination(['page' => 1, 'total' => 42]);
		$this->assertNull($pagination->prevPage());
	}

	public function testHasNextPage(): void
	{
		$pagination = new Pagination();
		$this->assertFalse($pagination->hasNextPage());

		$pagination = new Pagination(['total' => 42, 'page' => 3]);
		$this->assertFalse($pagination->hasNextPage());

		$pagination = new Pagination(['total' => 42, 'page' => 2]);
		$this->assertTrue($pagination->hasNextPage());
	}

	public function testNextPage(): void
	{
		$pagination = new Pagination(['page' => 1, 'total' => 30]);
		$this->assertSame(2, $pagination->nextPage());

		$pagination = new Pagination(['page' => 2, 'total' => 30]);
		$this->assertNull($pagination->nextPage());
	}

	public function testIsFirstPage(): void
	{
		$pagination = new Pagination();
		$this->assertTrue($pagination->isFirstPage());

		$pagination = new Pagination(['total' => 42]);
		$this->assertTrue($pagination->isFirstPage());

		$pagination = new Pagination(['total' => 42, 'page' => 2]);
		$this->assertFalse($pagination->isFirstPage());
	}

	public function testIsLastPage(): void
	{
		$pagination = new Pagination();
		$this->assertTrue($pagination->isLastPage());

		$pagination = new Pagination(['total' => 42]);
		$this->assertFalse($pagination->isLastPage());

		$pagination = new Pagination(['total' => 42, 'page' => 3]);
		$this->assertTrue($pagination->isLastPage());
	}

	public static function rangeProvider(): array
	{
		return [
			// at the beginning - even
			[[
				'page'     => 1,
				'total'    => 100,
				'limit'    => 1,
				'range'    => 10,
				'expected' => range(1, 10)
			]],
			// at the beginning - odd
			[[
				'page'     => 1,
				'total'    => 100,
				'limit'    => 1,
				'range'    => 5,
				'expected' => range(1, 5)
			]],
			// in the middle - even
			[[
				'page'     => 50,
				'total'    => 100,
				'limit'    => 1,
				'range'    => 10,
				'expected' => range(46, 55)
			]],
			// in the middle - odd
			[[
				'page'     => 50,
				'total'    => 100,
				'limit'    => 1,
				'range'    => 5,
				'expected' => range(48, 52)
			]],
			// at the end - even
			[[
				'page'     => 100,
				'total'    => 100,
				'limit'    => 1,
				'range'    => 10,
				'expected' => range(91, 100)
			]],
			// at the end - odd
			[[
				'page'     => 100,
				'total'    => 100,
				'limit'    => 1,
				'range'    => 5,
				'expected' => range(96, 100)
			]],
			// higher range than pages - even
			[[
				'page'     => 1,
				'total'    => 10,
				'limit'    => 1,
				'range'    => 12,
				'count'    => 10,
				'expected' => range(1, 10)
			]],
			// higher range than pages - odd
			[[
				'page'     => 1,
				'total'    => 10,
				'limit'    => 1,
				'range'    => 13,
				'count'    => 10,
				'expected' => range(1, 10)
			]],
			// case from forum - 1
			[[
				'page'     => 10,
				'total'    => 20,
				'limit'    => 2,
				'range'    => 5,
				'expected' => range(6, 10)
			]],
			// case from forum - 2
			[[
				'page'     => 3,
				'total'    => 20,
				'limit'    => 2,
				'range'    => 4,
				'expected' => range(2, 5)
			]]
		];
	}

	#[DataProvider('rangeProvider')]
	public function testRange(array $case): void
	{
		$pagination = new Pagination([
			'page'  => $case['page'],
			'total' => $case['total'],
			'limit' => $case['limit']
		]);

		$range = $pagination->range($case['range']);
		$start = A::first($case['expected']);
		$end   = A::last($case['expected']);

		$this->assertCount($case['count'] ?? $case['range'], $range);
		$this->assertSame($case['expected'], $range);
		$this->assertSame($start, $pagination->rangeStart($case['range']));
		$this->assertSame($end, $pagination->rangeEnd($case['range']));
	}

	public function testClone(): void
	{
		$pagination = new Pagination();
		$pagination = $pagination->clone(['limit' => 3, 'total' => 5, 'page' => 2]);

		$this->assertSame(3, $pagination->limit());
		$this->assertSame(5, $pagination->total());
		$this->assertSame(2, $pagination->page());
	}

	public function testCloneInvalid1(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid pagination limit: 0');

		$pagination = new Pagination();
		$pagination = $pagination->clone(['limit' => 0]);
	}

	public function testCloneInvalid2(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid total number of items: -1');

		$pagination = new Pagination();
		$pagination = $pagination->clone(['total' => -1]);
	}

	public function testCloneInvalid3(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid page number: -1');

		$pagination = new Pagination();
		$pagination = $pagination->clone(['page' => -1]);
	}

	public function testCloneOutOfBounds1(): void
	{
		$this->expectException(ErrorPageException::class);
		$this->expectExceptionMessage('Pagination page 3 does not exist, expected 1-2');

		$pagination = new Pagination();
		$pagination = $pagination->clone(['page' => 3, 'total' => 10, 'limit' => 5]);
	}

	public function testCloneOutOfBounds2(): void
	{
		$this->expectException(ErrorPageException::class);
		$this->expectExceptionMessage('Pagination page 0 does not exist, expected 1-2');

		$pagination = new Pagination();
		$pagination = $pagination->clone(['page' => 0, 'total' => 10, 'limit' => 5]);
	}

	public function testCloneOutOfBoundsNoValidate1(): void
	{
		Pagination::$validate = false;

		$pagination = new Pagination();
		$pagination = $pagination->clone(['page' => 3, 'total' => 10, 'limit' => 5]);

		$this->assertSame(2, $pagination->page());

		Pagination::$validate = true;
	}

	public function testCloneOutOfBoundsNoValidate2(): void
	{
		Pagination::$validate = false;

		$pagination = new Pagination();
		$pagination = $pagination->clone(['page' => 0, 'total' => 10, 'limit' => 5]);

		$this->assertSame(1, $pagination->page());

		Pagination::$validate = true;
	}

	public function testToArray(): void
	{
		$pagination = new Pagination();
		$keys = [
			'page',
			'firstPage',
			'lastPage',
			'pages',
			'offset',
			'limit',
			'total',
			'start',
			'end',
		];

		foreach ($keys as $key) {
			$this->assertArrayHasKey($key, $pagination->toArray());
		}
	}

	public function testForWithoutArguments(): void
	{
		$collection = new Collection(['a', 'b', 'c']);
		$pagination = Pagination::for($collection);

		$this->assertSame(1, $pagination->page());
		$this->assertSame(1, $pagination->pages());
		$this->assertSame(20, $pagination->limit());
		$this->assertSame(3, $pagination->total());
	}

	public function testForWithLimit(): void
	{
		$collection = new Collection(['a', 'b', 'c']);
		$pagination = Pagination::for($collection, 1);

		$this->assertSame(1, $pagination->page());
		$this->assertSame(3, $pagination->pages());
		$this->assertSame(1, $pagination->limit());
	}

	public function testForWithLimitAndPage(): void
	{
		$collection = new Collection(['a', 'b', 'c']);
		$pagination = Pagination::for($collection, 1, 2);

		$this->assertSame(2, $pagination->page());
		$this->assertSame(3, $pagination->pages());
		$this->assertSame(1, $pagination->limit());
	}

	public function testForWithOptionsArray(): void
	{
		$collection = new Collection(['a', 'b', 'c']);
		$pagination = Pagination::for($collection, [
			'limit' => 1,
			'page'  => 2
		]);

		$this->assertSame(2, $pagination->page());
		$this->assertSame(3, $pagination->pages());
		$this->assertSame(1, $pagination->limit());
	}

	public function testForWithLimitAndOptionsArray(): void
	{
		$collection = new Collection(['a', 'b', 'c']);
		$pagination = Pagination::for($collection, 1, [
			'page' => 2
		]);

		$this->assertSame(2, $pagination->page());
		$this->assertSame(3, $pagination->pages());
		$this->assertSame(1, $pagination->limit());
	}
}
