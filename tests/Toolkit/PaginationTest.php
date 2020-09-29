<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    public function setUp(): void
    {
        Pagination::$validate = true;
    }

    public function testDefaultPage()
    {
        $pagination = new Pagination();
        $this->assertEquals(0, $pagination->page());

        $pagination = new Pagination(['total' => 1]);
        $this->assertEquals(1, $pagination->page());
    }

    public function testPage()
    {
        $pagination = new Pagination(['total' => 100, 'page' => 2]);
        $this->assertEquals(2, $pagination->page());
    }

    public function testPageString()
    {
        $pagination = new Pagination(['total' => 100, 'page' => '2']);
        $this->assertEquals(2, $pagination->page());
    }

    public function testPageEmptyCollection()
    {
        $pagination = new Pagination(['total' => 0, 'page' => 1]);
        $this->assertEquals(0, $pagination->page());
    }

    public function testTotalDefault()
    {
        $pagination = new Pagination();
        $this->assertEquals(0, $pagination->total());
    }

    public function testTotal()
    {
        $pagination = new Pagination(['total' => 12]);
        $this->assertEquals(12, $pagination->total());
    }

    public function testLimitDefault()
    {
        $pagination = new Pagination();
        $this->assertEquals(20, $pagination->limit());
    }

    public function testLimit()
    {
        $pagination = new Pagination(['limit' => 100]);
        $this->assertEquals(100, $pagination->limit());
    }

    public function testStart()
    {
        $pagination = new Pagination([
            'total' => 42
        ]);

        $this->assertEquals(1, $pagination->start());

        // go to the second page
        $pagination = $pagination->clone(['page' => 2]);
        $this->assertEquals(21, $pagination->start());

        // set a different limit
        $pagination = $pagination->clone(['limit' => 10]);
        $this->assertEquals(11, $pagination->start());
    }

    public function testEnd()
    {
        $pagination = new Pagination([
            'total' => 42
        ]);

        $this->assertEquals(20, $pagination->end());

        // go to the second page
        $pagination = $pagination->clone(['page' => 2]);
        $this->assertEquals(40, $pagination->end());

        // set a different limit
        $pagination = $pagination->clone(['limit' => 10]);
        $this->assertEquals(20, $pagination->end());
    }

    public function testEndWithOneItem()
    {
        $pagination = new Pagination([
            'total' => 1
        ]);

        $this->assertEquals(1, $pagination->end());
    }

    public function testPages()
    {
        $pagination = new Pagination();
        $this->assertEquals(0, $pagination->pages());

        $pagination = new Pagination(['total' => 1]);
        $this->assertEquals(1, $pagination->pages());

        $pagination = new Pagination(['total' => 10]);
        $this->assertEquals(1, $pagination->pages());

        $pagination = new Pagination(['total' => 21]);
        $this->assertEquals(2, $pagination->pages());

        $pagination = new Pagination(['total' => 11, 'limit' => 5]);
        $this->assertEquals(3, $pagination->pages());
    }

    public function testFirstPage()
    {
        $pagination = new Pagination();
        $this->assertEquals(0, $pagination->firstPage());

        $pagination = new Pagination(['total' => 1]);
        $this->assertEquals(1, $pagination->firstPage());

        $pagination = new Pagination(['total' => 42]);
        $this->assertEquals(1, $pagination->firstPage());
    }

    public function testLastPage()
    {
        $pagination = new Pagination();
        $this->assertEquals(0, $pagination->lastPage());

        $pagination = new Pagination(['total' => 1]);
        $this->assertEquals(1, $pagination->lastPage());

        $pagination = new Pagination(['total' => 42]);
        $this->assertEquals(3, $pagination->lastPage());
    }

    public function testOffset()
    {
        $pagination = new Pagination();
        $this->assertEquals(0, $pagination->offset());

        $pagination = new Pagination(['total' => 42, 'page' => 2]);
        $this->assertEquals(20, $pagination->offset());

        $pagination = new Pagination(['total' => 42, 'page' => 2, 'limit' => 10]);
        $this->assertEquals(10, $pagination->offset());
    }

    public function testHasPage()
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

    public function testHasPages()
    {
        $pagination = new Pagination();
        $this->assertFalse($pagination->hasPages());

        $pagination = new Pagination(['total' => 1]);
        $this->assertFalse($pagination->hasPages());

        $pagination = new Pagination(['total' => 21]);
        $this->assertTrue($pagination->hasPages());
    }

    public function testHasPrevPage()
    {
        $pagination = new Pagination();
        $this->assertFalse($pagination->hasPrevPage());

        $pagination = new Pagination(['total' => 42, 'page' => 2]);
        $this->assertTrue($pagination->hasPrevPage());
    }

    public function testPrevPage()
    {
        $pagination = new Pagination(['page' => 2, 'total' => 42]);
        $this->assertEquals(1, $pagination->prevPage());

        $pagination = new Pagination(['page' => 1, 'total' => 42]);
        $this->assertEquals(null, $pagination->prevPage());
    }

    public function testHasNextPage()
    {
        $pagination = new Pagination();
        $this->assertFalse($pagination->hasNextPage());

        $pagination = new Pagination(['total' => 42, 'page' => 3]);
        $this->assertFalse($pagination->hasNextPage());

        $pagination = new Pagination(['total' => 42, 'page' => 2]);
        $this->assertTrue($pagination->hasNextPage());
    }

    public function testNextPage()
    {
        $pagination = new Pagination(['page' => 1, 'total' => 30]);
        $this->assertEquals(2, $pagination->nextPage());

        $pagination = new Pagination(['page' => 2, 'total' => 30]);
        $this->assertEquals(null, $pagination->nextPage());
    }

    public function testIsFirstPage()
    {
        $pagination = new Pagination();
        $this->assertTrue($pagination->isFirstPage());

        $pagination = new Pagination(['total' => 42]);
        $this->assertTrue($pagination->isFirstPage());

        $pagination = new Pagination(['total' => 42, 'page' => 2]);
        $this->assertFalse($pagination->isFirstPage());
    }

    public function testIsLastPage()
    {
        $pagination = new Pagination();
        $this->assertTrue($pagination->isLastPage());

        $pagination = new Pagination(['total' => 42]);
        $this->assertFalse($pagination->isLastPage());

        $pagination = new Pagination(['total' => 42, 'page' => 3]);
        $this->assertTrue($pagination->isLastPage());
    }

    public function rangeProvider()
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

    /**
     * @dataProvider rangeProvider
     */
    public function testRange($case)
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
        $this->assertEquals($case['expected'], $range);
        $this->assertEquals($start, $pagination->rangeStart($case['range']));
        $this->assertEquals($end, $pagination->rangeEnd($case['range']));
    }

    public function testClone()
    {
        $pagination = new Pagination();
        $pagination = $pagination->clone(['limit' => 3, 'total' => 5, 'page' => 2]);

        $this->assertSame(3, $pagination->limit());
        $this->assertSame(5, $pagination->total());
        $this->assertSame(2, $pagination->page());
    }

    public function testCloneInvalid1()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Invalid pagination limit: 0');

        $pagination = new Pagination();
        $pagination = $pagination->clone(['limit' => 0]);
    }

    public function testCloneInvalid2()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Invalid total number of items: -1');

        $pagination = new Pagination();
        $pagination = $pagination->clone(['total' => -1]);
    }

    public function testCloneInvalid3()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Invalid page number: -1');

        $pagination = new Pagination();
        $pagination = $pagination->clone(['page' => -1]);
    }

    public function testCloneOutOfBounds1()
    {
        $this->expectException('Kirby\Exception\ErrorPageException');
        $this->expectExceptionMessage('Pagination page 3 does not exist, expected 1-2');

        $pagination = new Pagination();
        $pagination = $pagination->clone(['page' => 3, 'total' => 10, 'limit' => 5]);
    }

    public function testCloneOutOfBounds2()
    {
        $this->expectException('Kirby\Exception\ErrorPageException');
        $this->expectExceptionMessage('Pagination page 0 does not exist, expected 1-2');

        $pagination = new Pagination();
        $pagination = $pagination->clone(['page' => 0, 'total' => 10, 'limit' => 5]);
    }

    public function testCloneOutOfBoundsNoValidate1()
    {
        Pagination::$validate = false;

        $pagination = new Pagination();
        $pagination = $pagination->clone(['page' => 3, 'total' => 10, 'limit' => 5]);

        $this->assertSame(2, $pagination->page());

        Pagination::$validate = true;
    }

    public function testCloneOutOfBoundsNoValidate2()
    {
        Pagination::$validate = false;

        $pagination = new Pagination();
        $pagination = $pagination->clone(['page' => 0, 'total' => 10, 'limit' => 5]);

        $this->assertSame(1, $pagination->page());

        Pagination::$validate = true;
    }

    public function testToArray()
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

    public function testForWithoutArguments()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $pagination = Pagination::for($collection);

        $this->assertEquals(1, $pagination->page());
        $this->assertEquals(1, $pagination->pages());
        $this->assertEquals(20, $pagination->limit());
        $this->assertEquals(3, $pagination->total());
    }

    public function testForWithLimit()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $pagination = Pagination::for($collection, 1);

        $this->assertEquals(1, $pagination->page());
        $this->assertEquals(3, $pagination->pages());
        $this->assertEquals(1, $pagination->limit());
    }

    public function testForWithLimitAndPage()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $pagination = Pagination::for($collection, 1, 2);

        $this->assertEquals(2, $pagination->page());
        $this->assertEquals(3, $pagination->pages());
        $this->assertEquals(1, $pagination->limit());
    }

    public function testForWithOptionsArray()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $pagination = Pagination::for($collection, [
            'limit' => 1,
            'page'  => 2
        ]);

        $this->assertEquals(2, $pagination->page());
        $this->assertEquals(3, $pagination->pages());
        $this->assertEquals(1, $pagination->limit());
    }

    public function testForWithLimitAndOptionsArray()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $pagination = Pagination::for($collection, 1, [
            'page' => 2
        ]);

        $this->assertEquals(2, $pagination->page());
        $this->assertEquals(3, $pagination->pages());
        $this->assertEquals(1, $pagination->limit());
    }
}
