<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\F;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PageSortTest extends TestCase
{

    protected $app;
    protected $fixtures;

    public function setUp()
    {
        $this->app = new App([
            'roots' => [
               'index' => $this->fixtures = __DIR__ . '/fixtures/PageSortTest'
            ]
        ]);

        $this->app->impersonate('kirby');

        Dir::make($this->fixtures);
    }

    public function tearDown()
    {
        Dir::remove($this->fixtures);
    }

    public function site()
    {
        return $this->app->site();
    }

    public function testChangeStatus()
    {
        $page = Page::create([
            'slug' => 'test',
        ]);

        $this->assertEquals('draft', $page->status());

        $listed = $page->changeStatus('listed');
        $this->assertEquals('listed', $listed->status());

        $unlisted = $page->changeStatus('unlisted');
        $this->assertEquals('unlisted', $unlisted->status());

        $draft = $page->changeStatus('draft');
        $this->assertEquals('draft', $draft->status());
    }

    public function testChangeStatusToInvalidStatus()
    {
        $page = Page::create([
            'slug' => 'test',
            'blueprint' => [
                'title'  => 'Test',
                'name'   => 'test',
                'status' => [
                    'draft'  => 'Draft',
                    'listed' => 'Published'
                ]
            ]
        ]);

        $this->assertEquals('draft', $page->status());

        $draft = $page->changeStatus('listed');
        $this->assertEquals('listed', $draft->status());

        $this->expectException(InvalidArgumentException::class);

        $unlisted = $page->changeStatus('unlisted');
        $this->assertEquals('unlisted', $unlisted->status());
    }

    public function testCreateDefaultNum()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug'     => 'one-child',
                        'children' => [
                            [
                                'slug' => 'child-a'
                            ]
                        ]
                    ],
                    [
                        'slug'     => 'three-children',
                        'children' => [
                            [
                                'slug' => 'child-a',
                                'num'  => 1
                            ],
                            [
                                'slug' => 'child-b',
                                'num'  => 2
                            ],
                            [
                                'slug' => 'child-c'
                            ]
                        ],
                        'drafts' => [
                            [
                                'slug' => 'draft'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // no siblings
        $page = $app->page('one-child/child-a');
        $this->assertEquals(1, $page->createNum());

        // two listed siblings / no position
        $page = $app->page('three-children/child-c');
        $this->assertEquals(3, $page->createNum());

        // one listed sibling / valid position
        $page = $app->page('three-children/child-a');
        $this->assertEquals(2, $page->createNum(2));

        // one listed sibling / position too low
        $page = $app->page('three-children/child-a');
        $this->assertEquals(1, $page->createNum(-1));

        // one listed sibling / position too high
        $page = $app->page('three-children/child-a');
        $this->assertEquals(2, $page->createNum(3));

        // draft / no position
        $page = $app->page('three-children/draft');
        $this->assertEquals(3, $page->createNum());

        // draft / given position
        $page = $app->page('three-children/draft');
        $this->assertEquals(1, $page->createNum(1));
    }

    public function testCreateZeroBasedNum()
    {
        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'num' => 'zero'
            ]
        ]);

        $this->assertEquals(0, $page->createNum());

        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'num' => 0
            ]
        ]);

        $this->assertEquals(0, $page->createNum());
    }

    public function testCreateDateBasedNum()
    {
        // without date
        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'num' => 'date'
            ]
        ]);

        $this->assertEquals(0, $page->createNum());

        // with date field
        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'num' => 'date',
            ],
            'content' => [
                'date' => '2012-12-12'
            ]
        ]);

        $this->assertEquals(20121212, $page->createNum());
    }

    public function testCreateCustomNum()
    {
        // valid
        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'num' => '{{ page.year }}'
            ],
            'content' => [
                'year' => 2016
            ]
        ]);

        $this->assertEquals(2016, $page->createNum());

        // invalid
        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'num' => '{{ page.year }}'
            ]
        ]);

        $this->assertEquals(0, $page->createNum());
    }

    public function testPublish()
    {
        // main page
        $page = Page::create([
            'slug' => 'test',
        ]);

        $site      = $this->app->site();
        $published = $page->publish();

        $this->assertEquals('unlisted', $published->status());

        $this->assertFalse($page->parentModel()->drafts()->has($published->id()));
        $this->assertTrue($page->parentModel()->children()->has($published->id()));

        $this->assertFalse($site->drafts()->has($published->id()));
        $this->assertTrue($site->children()->has($published->id()));

        // child
        $child = Page::create([
            'parent' => $page,
            'slug'   => 'child'
        ]);

        $published = $child->publish();

        $this->assertEquals('unlisted', $published->status());

        $this->assertFalse($child->parentModel()->drafts()->has($published->id()));
        $this->assertTrue($child->parentModel()->children()->has($published->id()));

        $this->assertFalse($page->drafts()->has($published->id()));
        $this->assertTrue($page->children()->has($published->id()));
    }

    public function testPublishAlreadyPublished()
    {
        $page = Page::create([
            'slug' => 'test'
        ]);

        $page = $page->publish();

        $this->assertEquals('unlisted', $page->status());
        $this->assertEquals('unlisted', $page->publish()->status());
    }

    public function testUpdateWithDateBasedNumbering()
    {
        $page = Page::create([
            'slug' => 'test',
            'num'  => 20121212,
            'blueprint' => [
                'title' => 'Test',
                'name'  => 'test',
                'num'   => 'date'
            ],
        ]);

        $this->assertEquals(20121212, $page->num());

        $modified = $page->update([
            'date' => '2016-11-21'
        ]);

        $this->assertEquals(20161121, $modified->num());
    }

}
