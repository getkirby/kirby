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

    public function testAddPage()
    {
        $pages = Pages::factory([
            ['slug' => 'a']
        ]);

        $page = new Page([
            'slug' => 'b'
        ]);

        $result = $pages->add($page);

        $this->assertCount(2, $result);
        $this->assertEquals('a', $result->nth(0)->slug());
        $this->assertEquals('b', $result->nth(1)->slug());
    }

    public function testAddCollection()
    {
        $a = Pages::factory([
            ['slug' => 'a']
        ]);

        $b = Pages::factory([
            ['slug' => 'b'],
            ['slug' => 'c']
        ]);

        $c = $a->add($b);

        $this->assertCount(3, $c);
        $this->assertEquals('a', $c->nth(0)->slug());
        $this->assertEquals('b', $c->nth(1)->slug());
        $this->assertEquals('c', $c->nth(2)->slug());
    }

    public function testAddById()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'children' => [
                            ['slug' => 'aa']
                        ]
                    ],
                    [
                        'slug' => 'b',
                    ]
                ]
            ]
        ]);

        $pages = $app->site()->children()->add('a/aa');

        $this->assertCount(3, $pages);
        $this->assertEquals('a', $pages->nth(0)->id());
        $this->assertEquals('b', $pages->nth(1)->id());
        $this->assertEquals('a/aa', $pages->nth(2)->id());
    }

    public function testAddNull()
    {
        $pages = new Pages();
        $this->assertCount(0, $pages);

        $pages->add(null);

        $this->assertCount(0, $pages);
    }

    public function testAddFalse()
    {
        $pages = new Pages();
        $this->assertCount(0, $pages);

        $pages->add(false);

        $this->assertCount(0, $pages);
    }

    public function testAddInvalidObject()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('You must pass a Page object to the Pages collection');

        $site  = new Site();
        $pages = new Pages();
        $pages->add($site);
    }

    public function testAudio()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'files' => [
                    ['filename' => 'a.mp3'],
                    ['filename' => 'a.pdf']
                ]
            ],
            [
                'slug' => 'b',
                'files' => [
                    ['filename' => 'b.mp3']
                ]
            ],
        ]);

        $this->assertEquals(['a.mp3', 'b.mp3'], $pages->audio()->pluck('filename'));
    }

    public function testCode()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'files' => [
                    ['filename' => 'a.js'],
                    ['filename' => 'a.pdf']
                ]
            ],
            [
                'slug' => 'b',
                'files' => [
                    ['filename' => 'b.js']
                ]
            ],
        ]);

        $this->assertEquals(['a.js', 'b.js'], $pages->code()->pluck('filename'));
    }

    public function testConstructWithCollection()
    {
        $pages = new Pages($this->pages()->not('a'));

        $this->assertCount(2, $pages);
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

    public function testDocuments()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'files' => [
                    ['filename' => 'a.pdf'],
                    ['filename' => 'a.js']
                ]
            ],
            [
                'slug' => 'b',
                'files' => [
                    ['filename' => 'b.pdf']
                ]
            ],
        ]);

        $this->assertEquals(['a.pdf', 'b.pdf'], $pages->documents()->pluck('filename'));
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

    public function testFiles()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'files' => [
                    ['filename' => 'a.jpg']
                ]
            ],
            [
                'slug' => 'b',
                'files' => [
                    ['filename' => 'b.pdf']
                ]
            ],
        ]);

        $this->assertEquals(['a.jpg', 'b.pdf'], $pages->files()->pluck('filename'));
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

    public function testImages()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'files' => [
                    ['filename' => 'a.jpg'],
                    ['filename' => 'a.pdf']
                ]
            ],
            [
                'slug' => 'b',
                'files' => [
                    ['filename' => 'b.png']
                ]
            ],
        ]);

        $this->assertEquals(['a.jpg', 'b.png'], $pages->images()->pluck('filename'));
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

    public function testNotTemplate()
    {
        $pages = Pages::factory([
            [
                'slug'     => 'a',
                'template' => 'a'
            ],
            [
                'slug'     => 'b',
                'template' => 'b'
            ],
            [
                'slug'     => 'c',
                'template' => 'c'
            ],
            [
                'slug'     => 'd',
                'template' => 'a'
            ],
        ]);

        $this->assertEquals(['a', 'b', 'c', 'd'], $pages->notTemplate(null)->pluck('slug'));
        $this->assertEquals(['b', 'c'], $pages->notTemplate('a')->pluck('slug'));
        $this->assertEquals(['c'], $pages->notTemplate(['a', 'b'])->pluck('slug'));
        $this->assertEquals(['a', 'b', 'c', 'd'], $pages->notTemplate(['z'])->pluck('slug'));
        $this->assertEquals([], $pages->notTemplate(['a', 'b', 'c'])->pluck('slug'));
    }

    public function testNums()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'num'  => 1
            ],
            [
                'slug' => 'b',
                'num'  => 2
            ],
        ]);

        $this->assertEquals([1, 2], $pages->nums());
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

    public function testSearchWords()
    {
        $pages = Pages::factory([
            [
                'slug'    => 'mtb',
                'content' => [
                    'title' => 'Mountainbike'
                ]
            ],
            [
                'slug'    => 'mountain',
                'content' => [
                    'title' => 'Mountain'
                ]
            ],
            [
                'slug'    => 'everest-mountain',
                'content' => [
                    'title' => 'Everest Mountain'
                ]
            ],
            [
                'slug'    => 'mount',
                'content' => [
                    'title' => 'Mount'
                ]
            ],
            [
                'slug'    => 'lakes',
                'content' => [
                    'title' => 'Lakes'
                ]
            ]
        ]);

        $result = $pages->search('mountain', ['words' => true]);
        $this->assertCount(2, $result);

        $result = $pages->search('mount', ['words' => false]);
        $this->assertCount(4, $result);
    }

    public function testCustomMethods()
    {
        Pages::$methods = [
            'test' => function () {
                $slugs = '';
                foreach ($this as $page) {
                    $slugs .= $page->slug();
                }
                return $slugs;
            }
        ];

        $pages = Pages::factory([
            [
                'slug' => 'page',
                'children' => [
                    ['slug' => 'a'],
                    ['slug' => 'b']
                ]
            ]
        ]);

        $pages = $pages->find('page')->children();
        $this->assertEquals('ab', $pages->test());

        Pages::$methods = [];
    }

    public function testTemplate()
    {
        $pages = Pages::factory([
            [
                'slug'     => 'a',
                'template' => 'a'
            ],
            [
                'slug'     => 'b',
                'template' => 'b'
            ],
            [
                'slug'     => 'c',
                'template' => 'a'
            ],
        ]);

        $this->assertEquals(['a', 'b', 'c'], $pages->template(null)->pluck('slug'));
        $this->assertEquals(['a', 'c'], $pages->template('a')->pluck('slug'));
        $this->assertEquals(['a', 'b', 'c'], $pages->template(['a', 'b'])->pluck('slug'));
    }

    public function testVideos()
    {
        $pages = Pages::factory([
            [
                'slug' => 'a',
                'files' => [
                    ['filename' => 'a.mov'],
                    ['filename' => 'a.pdf']
                ]
            ],
            [
                'slug' => 'b',
                'files' => [
                    ['filename' => 'b.mp4']
                ]
            ],
        ]);

        $this->assertEquals(['a.mov', 'b.mp4'], $pages->videos()->pluck('filename'));
    }
}
