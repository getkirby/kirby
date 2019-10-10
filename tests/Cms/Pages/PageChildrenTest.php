<?php

namespace Kirby\Cms;

class PageChildrenTest extends TestCase
{
    public function testDefaultChildren()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertInstanceOf(Pages::class, $page->children());
        $this->assertCount(0, $page->children());
    }

    public function testGrandChildren()
    {
        $page = new Page([
            'slug' => 'grandma',
            'children' => [
                [
                    'slug' => 'mother',
                    'children' => [
                        ['slug' => 'child']
                    ]
                ]
            ]
        ]);

        $this->assertCount(1, $page->grandChildren());
        $this->assertEquals('child', $page->grandChildren()->first()->slug());
    }

    public function testHasChildren()
    {
        $page = new Page([
            'slug' => 'test',
            'children' => [
                ['slug' => 'a'],
                ['slug' => 'b']
            ]
        ]);

        $this->assertTrue($page->hasChildren());
    }

    public function testHasNoChildren()
    {
        $page = new Page([
            'slug'     => 'test',
            'children' => []
        ]);

        $this->assertFalse($page->hasChildren());
    }

    public function testHasListedChildren()
    {
        $page = new Page([
            'slug'     => 'test',
            'children' => [
                ['slug' => 'a', 'num' => 1]
            ]
        ]);

        $this->assertTrue($page->hasListedChildren());
    }

    public function testHasNoListedChildren()
    {
        $page = new Page([
            'slug'     => 'test',
            'children' => [
                ['slug' => 'a']
            ]
        ]);

        $this->assertFalse($page->hasListedChildren());
    }

    public function testHasUnlistedChildren()
    {
        $page = new Page([
            'slug'     => 'test',
            'children' => [
                ['slug' => 'a']
            ]
        ]);

        $this->assertTrue($page->hasUnlistedChildren());
    }

    public function testHasNoUnlistedChildren()
    {
        $page = new Page([
            'slug'     => 'test',
            'children' => [
                ['slug' => 'a', 'num' => 1]
            ]
        ]);

        $this->assertFalse($page->hasUnlistedChildren());
    }

    public function testHasDrafts()
    {
        $page = new Page([
            'slug' => 'test',
            'drafts' => [
                ['slug' => 'a'],
                ['slug' => 'b']
            ]
        ]);

        $this->assertTrue($page->hasDrafts());
    }

    public function testHasNoDrafts()
    {
        $page = new Page([
            'slug' => 'test',
        ]);

        $this->assertFalse($page->hasDrafts());
    }
}
