<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PageSiblingsTest extends TestCase
{

    public function setUp()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    protected function collection()
    {
        return [
            ['slug' => 'project-a'],
            ['slug' => 'project-b'],
            ['slug' => 'project-c']
        ];
    }

    public function testDefaultCollectionWithoutSite()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertInstanceOf(Pages::class, $page->collection());
    }

    public function testCollection()
    {
        $pages = new Pages([]);
        $page  = new Page(['slug' => 'test', 'collection' => $pages]);

        $this->assertEquals($pages, $page->collection());
    }

    public function testCollectionContext()
    {
        $collection = Pages::factory($this->collection());
        $page       = $collection->first();

        $this->assertEquals($collection, $page->collection());
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidCollection()
    {
        $page = new Page([
            'slug'       => 'test',
            'collection' => 'collection'
        ]);
    }

    public function testHasNext()
    {
        $collection = Pages::factory($this->collection());

        $this->assertTrue($collection->first()->hasNext());
        $this->assertFalse($collection->last()->hasNext());
    }

    public function testHasNextListed()
    {
        $collection = Pages::factory([
            ['slug' => 'unlisted'],
            ['slug' => 'listed', 'num' => 1],
        ]);

        $this->assertTrue($collection->first()->hasNextListed());
        $this->assertFalse($collection->last()->hasNextListed());
    }

    public function testHasNextUnlisted()
    {
        $collection = Pages::factory([
            ['slug' => 'listed', 'num' => 1],
            ['slug' => 'unlisted'],
        ]);

        $this->assertTrue($collection->first()->hasNextUnlisted());
        $this->assertFalse($collection->last()->hasNextUnlisted());
    }

    public function testHasPrev()
    {
        $collection = Pages::factory($this->collection());

        $this->assertTrue($collection->last()->hasPrev());
        $this->assertFalse($collection->first()->hasPrev());
    }

    public function testHasPrevListed()
    {
        $collection = Pages::factory([
            ['slug' => 'listed', 'num' => 1],
            ['slug' => 'unlisted'],
        ]);

        $this->assertFalse($collection->first()->hasPrevListed());
        $this->assertTrue($collection->last()->hasPrevListed());
    }

    public function testHasPrevUnlisted()
    {
        $collection = Pages::factory([
            ['slug' => 'unlisted'],
            ['slug' => 'listed', 'num' => 1]
        ]);

        $this->assertFalse($collection->first()->hasPrevUnlisted());
        $this->assertTrue($collection->last()->hasPrevUnlisted());
    }

    public function testIndexOf()
    {
        $collection = Pages::factory($this->collection());

        $this->assertEquals(0, $collection->first()->indexOf());
        $this->assertEquals(1, $collection->nth(1)->indexOf());
        $this->assertEquals(2, $collection->last()->indexOf());
    }

    public function testIsFirst()
    {
        $collection = Pages::factory($this->collection());

        $this->assertTrue($collection->first()->isFirst());
        $this->assertFalse($collection->last()->isFirst());
    }

    public function testIsLast()
    {
        $collection = Pages::factory($this->collection());

        $this->assertTrue($collection->last()->isLast());
        $this->assertFalse($collection->first()->isLast());
    }

    public function testIsNth()
    {
        $collection = Pages::factory($this->collection());

        $this->assertTrue($collection->first()->isNth(0));
        $this->assertTrue($collection->nth(1)->isNth(1));
        $this->assertTrue($collection->last()->isNth($collection->count() - 1));
    }

    public function testNext()
    {
        $collection = Pages::factory($this->collection());

        $this->assertEquals($collection->first()->next(), $collection->nth(1));
    }

    public function testNextAll()
    {
        $collection = Pages::factory($this->collection());
        $first      = $collection->first();

        $this->assertCount(2, $first->nextAll());

        $this->assertEquals($first->nextAll()->first(), $collection->nth(1));
        $this->assertEquals($first->nextAll()->last(),  $collection->nth(2));
    }

    public function testNextListed()
    {
        $collection = Pages::factory([
            ['slug' => 'unlisted-a'],
            ['slug' => 'unlisted-b'],
            ['slug' => 'listed', 'num' => 1],
        ]);

        $this->assertEquals('listed', $collection->first()->nextListed()->slug());
    }

    public function testNextUnlisted()
    {
        $collection = Pages::factory([
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'listed-b', 'num' => 2],
            ['slug' => 'unlisted'],
        ]);

        $this->assertEquals('unlisted', $collection->first()->nextUnlisted()->slug());
    }

    public function testPrev()
    {
        $collection = Pages::factory($this->collection());

        $this->assertEquals($collection->last()->prev(), $collection->nth(1));
    }

    public function testPrevAll()
    {
        $collection = Pages::factory($this->collection());
        $last       = $collection->last();

        $this->assertCount(2, $last->prevAll());

        $this->assertEquals($last->prevAll()->first(), $collection->nth(0));
        $this->assertEquals($last->prevAll()->last(),  $collection->nth(1));
    }

    public function testPrevListed()
    {
        $collection = Pages::factory([
            ['slug' => 'listed', 'num' => 1],
            ['slug' => 'unlisted-a'],
            ['slug' => 'unlisted-b'],
        ]);

        $this->assertEquals('listed', $collection->last()->prevListed()->slug());
    }

    public function testPrevUnlisted()
    {
        $collection = Pages::factory([
            ['slug' => 'unlisted'],
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'listed-b', 'num' => 2],
        ]);

        $this->assertEquals('unlisted', $collection->last()->prevUnlisted()->slug());
    }

    public function testSiblings()
    {
        $site = new Site([
            'children' => $this->collection()
        ]);

        $page     = $site->children()->nth(1);
        $children = $site->children();
        $siblings = $children->not($page);

        $this->assertEquals($children, $page->siblings());
        $this->assertEquals($siblings, $page->siblings(false));
    }

    public function testTemplateSiblings()
    {
        $pages = Pages::factory([
            [
                'slug'     => 'a',
                'template' => 'project'
            ],
            [
                'slug'     => 'b',
                'template' => 'article'
            ],
            [
                'slug'     => 'c',
                'template' => 'project'
            ],
            [
                'slug'     => 'd',
                'template' => 'project'
            ]
        ]);

        $siblings = $pages->first()->templateSiblings();

        $this->assertTrue($siblings->has('a'));
        $this->assertTrue($siblings->has('c'));
        $this->assertTrue($siblings->has('d'));

        $this->assertFalse($siblings->has('b'));

        $siblings = $pages->first()->templateSiblings(false);

        $this->assertTrue($siblings->has('c'));
        $this->assertTrue($siblings->has('d'));

        $this->assertFalse($siblings->has('a'));
        $this->assertFalse($siblings->has('b'));

    }

}
