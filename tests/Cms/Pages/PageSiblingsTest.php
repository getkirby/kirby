<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PageSiblingsTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    protected function site($children = null)
    {
        $this->app = $this->app->clone([
            'site' => [
                'children' => $children ?? $this->collection(),
            ]
        ]);

        return $this->app->site();
    }

    protected function collection()
    {
        return [
            ['slug' => 'project-a'],
            ['slug' => 'project-b'],
            ['slug' => 'project-c']
        ];
    }

    public function testDefaultSiblings()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertInstanceOf(Pages::class, $page->siblings());
    }

    public function testHasNext()
    {
        $children = $this->site()->children();

        $this->assertTrue($children->first()->hasNext());
        $this->assertFalse($children->last()->hasNext());
    }

    public function testHasNextCustomCollection()
    {
        $children = $this->site()->children();
        $page = $children->first();

        $this->assertTrue($page->hasNext());
        $this->assertFalse($page->hasNext($children->flip()));
    }

    public function testHasNextListed()
    {
        $site = $this->site([
            ['slug' => 'unlisted-a'],
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'unlisted-b'],
            ['slug' => 'listed-b', 'num' => 2],
        ]);

        $collection = $site->children();

        $this->assertTrue($collection->first()->hasNextListed());
        $this->assertFalse($collection->last()->hasNextListed());
    }

    public function testHasNextUnlisted()
    {
        $site = $this->site([
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'unlisted-a'],
            ['slug' => 'listed-b', 'num' => 2],
            ['slug' => 'unlisted-b'],
        ]);

        $collection = $site->children();

        $this->assertTrue($collection->first()->hasNextUnlisted());
        $this->assertFalse($collection->last()->hasNextUnlisted());
    }

    public function testHasPrev()
    {
        $collection = $this->site()->children();

        $this->assertTrue($collection->last()->hasPrev());
        $this->assertFalse($collection->first()->hasPrev());
    }

    public function testHasPrevCustomCollection()
    {
        $children = $this->site()->children();
        $page = $children->last();

        $this->assertTrue($page->hasPrev());
        $this->assertFalse($page->hasPrev($children->flip()));
    }

    public function testHasPrevListed()
    {
        $site = $this->site([
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'unlisted-a'],
            ['slug' => 'listed-b', 'num' => 2],
            ['slug' => 'unlisted-b'],
        ]);

        $collection = $site->children();

        $this->assertFalse($collection->first()->hasPrevListed());
        $this->assertTrue($collection->last()->hasPrevListed());
    }

    public function testHasPrevUnlisted()
    {
        $site = $this->site([
            ['slug' => 'unlisted-a'],
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'unlisted-b'],
            ['slug' => 'listed-b', 'num' => 2],
        ]);

        $collection = $site->children();

        $this->assertFalse($collection->first()->hasPrevUnlisted());
        $this->assertTrue($collection->last()->hasPrevUnlisted());
    }

    public function testIndexOf()
    {
        $collection = $this->site()->children();

        $this->assertEquals(0, $collection->first()->indexOf());
        $this->assertEquals(1, $collection->nth(1)->indexOf());
        $this->assertEquals(2, $collection->last()->indexOf());
    }

    public function testIndexOfCustomCollection()
    {
        $collection = $this->site()->children();
        $page = $collection->first();

        $this->assertEquals(0, $page->indexOf());
        $this->assertEquals(2, $page->indexOf($collection->flip()));
    }

    public function testIsFirst()
    {
        $collection = $this->site()->children();

        $this->assertTrue($collection->first()->isFirst());
        $this->assertFalse($collection->last()->isFirst());
    }

    public function testIsLast()
    {
        $collection = $this->site()->children();

        $this->assertTrue($collection->last()->isLast());
        $this->assertFalse($collection->first()->isLast());
    }

    public function testIsNth()
    {
        $collection = $this->site()->children();

        $this->assertTrue($collection->first()->isNth(0));
        $this->assertTrue($collection->nth(1)->isNth(1));
        $this->assertTrue($collection->last()->isNth($collection->count() - 1));
    }

    public function testNext()
    {
        $collection = $this->site()->children();

        $this->assertEquals($collection->first()->next(), $collection->nth(1));
    }

    public function testNextAll()
    {
        $collection = $this->site()->children();
        $first      = $collection->first();

        $this->assertCount(2, $first->nextAll());

        $this->assertEquals($first->nextAll()->first(), $collection->nth(1));
        $this->assertEquals($first->nextAll()->last(), $collection->nth(2));
    }

    public function testNextListed()
    {
        $collection = $this->site([
            ['slug' => 'unlisted-a'],
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'unlisted-b'],
            ['slug' => 'listed-b', 'num' => 2],
        ])->children();

        $this->assertSame('listed-a', $collection->first()->nextListed()->slug());
    }

    public function testNextUnlisted()
    {
        $collection = $this->site([
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'unlisted-a'],
            ['slug' => 'listed-b', 'num' => 2],
            ['slug' => 'unlisted-b'],
        ])->children();

        $this->assertSame('unlisted-a', $collection->first()->nextUnlisted()->slug());
    }

    public function testPrev()
    {
        $collection = $this->site()->children();

        $this->assertEquals($collection->last()->prev(), $collection->nth(1));
    }

    public function testPrevAll()
    {
        $collection = $this->site()->children();
        $last       = $collection->last();

        $this->assertCount(2, $last->prevAll());

        $this->assertEquals($last->prevAll()->first(), $collection->nth(0));
        $this->assertEquals($last->prevAll()->last(), $collection->nth(1));
    }

    public function testPrevListed()
    {
        $collection = $this->site([
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'unlisted-a'],
            ['slug' => 'listed-b', 'num' => 1],
            ['slug' => 'unlisted-b'],
        ])->children();

        $this->assertSame('listed-b', $collection->last()->prevListed()->slug());
    }

    public function testPrevUnlisted()
    {
        $collection = $this->site([
            ['slug' => 'unlisted-a'],
            ['slug' => 'listed-a', 'num' => 1],
            ['slug' => 'unlisted-b'],
            ['slug' => 'listed-b', 'num' => 2],
        ])->children();

        $this->assertSame('unlisted-b', $collection->last()->prevUnlisted()->slug());
    }

    public function testSiblings()
    {
        $site     = $this->site();
        $page     = $site->children()->nth(1);
        $children = $site->children();
        $siblings = $children->not($page);

        $this->assertEquals($children, $page->siblings());
        $this->assertEquals($siblings, $page->siblings(false));
    }

    public function testDraftSiblings()
    {
        $parent = new Page([
            'slug' => 'parent',
            'children' => [
                ['slug' => 'a'],
                ['slug' => 'b'],
            ],
            'drafts' => [
                ['slug' => 'c'],
                ['slug' => 'd'],
                ['slug' => 'e'],
            ]
        ]);

        $drafts = $parent->drafts();
        $draft  = $drafts->find('c');

        $this->assertEquals($drafts, $draft->siblings());
    }

    public function testTemplateSiblings()
    {
        $site = $this->site([
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

        $pages    = $site->children();
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
