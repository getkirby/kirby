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

    public function testFindWithExtension()
    {
        $this->assertIsPage($this->pages()->find('a.xml'), 'a');
        $this->assertIsPage($this->pages()->find('b.json'), 'b');
    }

    public function testFindByIdAndUri()
    {
        $site = new Site([
            'children' => [
                [
                    'slug' => 'grandma',
                    'children' => [
                        [
                            'slug' => 'mother',
                            'children' => [
                                [
                                    'slug' => 'child',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertIsPage($site->children()->findById('grandma'), 'grandma');
        $this->assertIsPage($site->children()->findById('grandma/'), 'grandma');
        $this->assertIsPage($site->children()->findByUri('grandma'), 'grandma');
        $this->assertIsPage($site->children()->findByUri('grandma/'), 'grandma');
        $this->assertIsPage($site->children()->findByUri('grandma.json'), 'grandma');
        $this->assertIsPage($site->children()->findById('grandma/mother'), 'grandma/mother');
        $this->assertIsPage($site->children()->findById('grandma/mother/'), 'grandma/mother');
        $this->assertIsPage($site->children()->findByUri('grandma/mother'), 'grandma/mother');
        $this->assertIsPage($site->children()->findByUri('grandma/mother/'), 'grandma/mother');
        $this->assertIsPage($site->children()->findById('grandma/mother.json'), 'grandma/mother');
        $this->assertIsPage($site->children()->findById('grandma/mother/child'), 'grandma/mother/child');
        $this->assertIsPage($site->children()->findById('grandma/mother/child/'), 'grandma/mother/child');
        $this->assertIsPage($site->children()->findByUri('grandma/mother/child'), 'grandma/mother/child');
        $this->assertIsPage($site->children()->findByUri('grandma/mother/child/'), 'grandma/mother/child');
        $this->assertIsPage($site->children()->findByUri('grandma/mother/child.json'), 'grandma/mother/child');
    }

    public function testFindMultiple()
    {
        $pages = Pages::factory([
            [
                'slug' => 'page',
                'children' => [
                    ['slug' => 'a'],
                    ['slug' => 'b'],
                    ['slug' => 'c']
                ]
            ]
        ]);

        $collection = $pages->find('page')->children()->find('a', 'c');
        $page       = $pages->find('page')->children()->last();

        $this->assertTrue($collection->has($page));
    }

    public function testIndex()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'children' => [
                    [
                        'slug' => 'aa',
                        'children' => [
                            ['slug' => 'aaa'],
                            ['slug' => 'aab'],
                        ]
                    ],
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
            'a',
            'a/aa',
            'a/aa/aaa',
            'a/aa/aab',
            'a/ab',
            'b',
            'b/ba',
            'b/bb',
        ];

        $this->assertEquals($expected, $pages->index()->keys());
    }

    public function testIndexWithDrafts()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'children' => [
                    [
                        'slug' => 'aa',
                        'children' => [
                            ['slug' => 'aaa'],
                            ['slug' => 'aab'],
                        ]
                    ],
                    [
                        'slug' => 'ab'
                    ]
                ],
                'drafts' => [
                    [
                        'slug' => 'ac'
                    ]
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
            'a',
            'a/aa',
            'a/aa/aaa',
            'a/aa/aab',
            'a/ab',
            'a/ac',
            'b',
            'b/ba',
            'b/bb',
        ];

        $this->assertEquals($expected, $pages->index(true)->keys());
    }

    public function testInvisible()
    {
        $this->assertCount(1, $this->pages()->invisible());
    }

    public function testVisible()
    {
        $this->assertCount(2, $this->pages()->visible());
    }

    public function testListed()
    {
        $this->assertCount(2, $this->pages()->listed());
    }

    public function testUnlisted()
    {
        $this->assertCount(1, $this->pages()->unlisted());
    }

    public function testPublished()
    {
        $this->assertCount(3, $this->pages()->published());
    }

    public function testSearch()
    {
        $pages = Pages::factory([
            [
                'slug'    => 'mtb',
                'content' => [
                    'title' => 'Mountainbike'
                ]
            ],
            [
                'slug'    => 'mountains',
                'content' => [
                    'title' => 'Mountains'
                ]
            ],
            [
                'slug'    => 'lakes',
                'content' => [
                    'title' => 'Lakes'
                ]
            ]
        ]);

        $result = $pages->search('mountain');
        $this->assertCount(2, $result);
    }
}
