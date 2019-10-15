<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PagePickerTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'grandmother',
                        'children' => [
                            [
                                'slug' => 'mother',
                                'children' => [
                                    ['slug' => 'child-a'],
                                    ['slug' => 'child-b'],
                                    ['slug' => 'child-c']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->app->impersonate('kirby');
    }

    public function testDefaults()
    {
        $picker = new PagePicker();

        $this->assertEquals($this->app->site(), $picker->model());
        $this->assertCount(1, $picker->items());
        $this->assertEquals('grandmother', $picker->items()->first()->id());
    }

    public function testParent()
    {
        $picker = new PagePicker([
            'parent' => 'grandmother'
        ]);

        $this->assertCount(1, $picker->items());
        $this->assertEquals('grandmother/mother', $picker->items()->first()->id());
        $this->assertEquals('grandmother', $picker->model()->id());
    }

    public function testParentStart()
    {
        $picker = new PagePicker([
            'parent' => 'grandmother/mother'
        ]);

        $this->assertEquals($picker->start(), $this->app->site());
    }

    public function testQuery()
    {
        $picker = new PagePicker([
            'query' => 'site.find("grandmother/mother").children'
        ]);

        $this->assertCount(3, $picker->items());
        $this->assertEquals('grandmother/mother/child-a', $picker->items()->first()->id());
        $this->assertEquals('grandmother/mother/child-c', $picker->items()->last()->id());
    }

    public function testQueryAndParent()
    {
        $picker = new PagePicker([
            'query'  => 'site.find("grandmother").children',
            'parent' => 'grandmother/mother'
        ]);

        $this->assertCount(3, $picker->items());
        $this->assertEquals('grandmother/mother/child-a', $picker->items()->first()->id());
        $this->assertEquals('grandmother/mother/child-c', $picker->items()->last()->id());
    }

    public function testQueryStart()
    {
        $picker = new PagePicker([
            'query'  => 'site.find("grandmother").children',
            'parent' => 'grandmother/mother'
        ]);

        $this->assertEquals('grandmother', $picker->start()->id());
    }
}
