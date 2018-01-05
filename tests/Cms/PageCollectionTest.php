<?php

namespace Kirby\Cms;

class PageCollectionTest extends TestCase
{

    protected function collection()
    {
        return new Pages([
            new Page(['id' => 'project-a']),
            new Page(['id' => 'project-b']),
            new Page(['id' => 'project-c'])
        ]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "site" does not exist
     */
    public function testDefaultCollectionWithoutSite()
    {
        $page = new Page(['id' =>  'test']);
        $this->assertInstanceOf(Pages::class, $page->collection());
    }

    public function testDefaultCollectionWithSite()
    {
        $page     = new Page(['id' => 'test']);
        $children = new Children([$page]);
        $site     = new Site(['children' => $children]);

        Page::use('site', $site);

        $this->assertEquals($children, $page->collection());
    }

    public function testCollection()
    {
        $pages = new Pages([]);
        $page  = new Page(['id' => 'test', 'collection' => $pages]);

        $this->assertEquals($pages, $page->collection());
    }

    public function testCollectionContext()
    {
        $collection = $this->collection();
        $page       = $collection->first();

        $this->assertEquals($collection, $page->collection());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "collection" property must be of type "Kirby\Cms\Pages"
     */
    public function testInvalidCollection()
    {
        $page = new Page([
            'id'         => 'test',
            'collection' => 'collection'
        ]);
    }

    public function testHasNext()
    {
        $collection = $this->collection();

        $this->assertTrue($collection->first()->hasNext());
        $this->assertFalse($collection->last()->hasNext());
    }

    public function testHasNextInvisible()
    {
        $this->markTestIncomplete();
    }

    public function testHasNextVisible()
    {
        $this->markTestIncomplete();
    }

    public function testHasPrev()
    {
        $collection = $this->collection();

        $this->assertTrue($collection->last()->hasPrev());
        $this->assertFalse($collection->first()->hasPrev());
    }

    public function testHasPrevInvisible()
    {
        $this->markTestIncomplete();
    }

    public function testHasPrevVisible()
    {
        $this->markTestIncomplete();
    }

    public function testIndexOf()
    {
        $collection = $this->collection();

        $this->assertEquals(0, $collection->first()->indexOf());
        $this->assertEquals(1, $collection->nth(1)->indexOf());
        $this->assertEquals(2, $collection->last()->indexOf());
    }

    public function testIsFirst()
    {
        $collection = $this->collection();

        $this->assertTrue($collection->first()->isFirst());
        $this->assertFalse($collection->last()->isFirst());
    }

    public function testIsLast()
    {
        $collection = $this->collection();

        $this->assertTrue($collection->last()->isLast());
        $this->assertFalse($collection->first()->isLast());
    }

    public function testIsNth()
    {
        $collection = $this->collection();

        $this->assertTrue($collection->first()->isNth(0));
        $this->assertTrue($collection->nth(1)->isNth(1));
        $this->assertTrue($collection->last()->isNth($collection->count() - 1));
    }

    public function testNext()
    {
        $collection = $this->collection();

        $this->assertEquals($collection->first()->next(), $collection->nth(1));
    }

    public function testNextAll()
    {
        $collection = $this->collection();
        $first      = $collection->first();

        $this->assertCount(2, $first->nextAll());

        $this->assertEquals($first->nextAll()->first(), $collection->nth(1));
        $this->assertEquals($first->nextAll()->last(),  $collection->nth(2));
    }

    public function testNextInvisible()
    {
        $this->markTestIncomplete();
    }

    public function testNextVisible()
    {
        $this->markTestIncomplete();
    }

    public function testPrev()
    {
        $collection = $this->collection();

        $this->assertEquals($collection->last()->prev(), $collection->nth(1));
    }

    public function testPrevAll()
    {
        $collection = $this->collection();
        $last       = $collection->last();

        $this->assertCount(2, $last->prevAll());

        $this->assertEquals($last->prevAll()->first(), $collection->nth(0));
        $this->assertEquals($last->prevAll()->last(),  $collection->nth(1));
    }

    public function testPrevInvisible()
    {
        $this->markTestIncomplete();
    }

    public function testPrevVisible()
    {
        $this->markTestIncomplete();
    }

    public function testSiblings()
    {

        $children = $this->collection();

        $site = new Site([
            'children' => $children
        ]);

        Page::use('site', $site);

        $page = $site->children()->nth(1);

        $this->assertEquals($children, $page->siblings());
        $this->assertEquals($children, $children->first()->siblings());
        $this->assertEquals($children, $children->last()->siblings());

    }

    public function testSiblingsWithoutCurrentPage()
    {
        $this->markTestIncomplete();
    }

}
