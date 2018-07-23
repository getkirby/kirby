<?php

namespace Kirby\Cms;

class PagesTest extends TestCase
{

    public function pages()
    {
        return new Pages([
            new Page(['slug' => 'a', 'num' => 1]),
            new Page(['slug' => 'b', 'num' => 2]),
            new Page(['slug' => 'c'])
        ]);
    }

    public function testChildren()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'children' => [
                    ['slug' => 'aa'],
                    ['slug' => 'ab']
                ]
            ],
            [
                'slug' => 'b',
                'children' => [
                    ['slug' => 'ba'],
                    ['slug' => 'bb']
                ]
            ]
        ]);

        $expected = [
            'a/aa',
            'a/ab',
            'b/ba',
            'b/bb',
        ];

        $this->assertEquals($expected, $pages->children()->keys());
    }

    public function testDrafts()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'drafts' => [
                    ['slug' => 'aa'],
                    ['slug' => 'ab']
                ]
            ],
            [
                'slug' => 'b',
                'drafts' => [
                    ['slug' => 'ba'],
                    ['slug' => 'bb']
                ]
            ]
        ]);

        $expected = [
            'a/aa',
            'a/ab',
            'b/ba',
            'b/bb',
        ];

        $this->assertEquals($expected, $pages->drafts()->keys());
    }

    public function testFind()
    {
        $this->assertIsPage($this->pages()->find('a'), 'a');
        $this->assertIsPage($this->pages()->find('b'), 'b');
        $this->assertIsPage($this->pages()->find('c'), 'c');
    }

    public function testInvisible()
    {
        $this->assertCount(1, $this->pages()->invisible());
    }

    public function testVisible()
    {
        $this->assertCount(2, $this->pages()->visible());
    }

}
