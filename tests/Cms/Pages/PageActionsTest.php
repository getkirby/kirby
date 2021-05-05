<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;

class PageActionsTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/PageActionsTest'
            ],
        ]);

        $this->app->impersonate('kirby');

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function site()
    {
        return $this->app->site();
    }

    public function slugProvider()
    {
        return [
            ['modified-test', 'modified-test', true],
            ['modified-test', 'modified-test', false],
            ['mödified-tést', 'modified-test', true],
            ['mödified-tést', 'modified-test', false]
        ];
    }

    public function testChangeNum()
    {
        $phpunit = $this;

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'hooks' => [
                'page.changeNum:before' => function ($page, $num) use ($phpunit) {
                    $phpunit->assertEquals(2, $num);
                },
                'page.changeNum:after' => function ($newPage, $oldPage) use ($phpunit) {
                    $phpunit->assertEquals(1, $oldPage->num());
                    $phpunit->assertEquals(2, $newPage->num());
                }
            ]
        ]);

        $page = new Page([
            'slug' => 'test',
            'num'  => 1
        ]);

        $updatedPage = $page->changeNum(2);

        $this->assertNotEquals($page, $updatedPage);
        $this->assertEquals(2, $updatedPage->num());
    }

    public function testChangeNumWhenNumStaysTheSame()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'hooks' => [
                'page.changeNum:before' => function () {
                    throw new \Exception('This should not be called');
                }
            ]
        ]);

        $page = new Page([
            'slug' => 'test',
            'num'  => 1
        ]);

        // the result page should stay the same
        $this->assertEquals($page, $page->changeNum(1));
    }

    /**
     * @dataProvider slugProvider
     */
    public function testChangeSlug($input, $expected, $draft)
    {
        if ($draft) {
            $page = Page::create([
                'slug' => 'test',
            ]);

            $in      = 'drafts';
            $oldRoot = $this->fixtures . '/content/_drafts/test';
            $newRoot = $this->fixtures . '/content/_drafts/' . $expected;
        } else {
            $page = Page::create([
                'slug' => 'test',
                'num'  => 1
            ]);

            $in      = 'children';
            $oldRoot = $this->fixtures . '/content/1_test';
            $newRoot = $this->fixtures . '/content/1_' . $expected;
        }

        $this->assertTrue($page->exists());
        $this->assertEquals('test', $page->slug());

        $this->assertTrue($page->parentModel()->$in()->has('test'));
        $this->assertEquals($oldRoot, $page->root());

        $modified = $page->changeSlug($input);

        $this->assertTrue($modified->exists());
        $this->assertEquals($expected, $modified->slug());
        $this->assertTrue($modified->parentModel()->$in()->has($expected));
        $this->assertEquals($newRoot, $modified->root());
    }

    public function testChangeTemplate()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'blueprints' => [
                'pages/video' => [
                    'title'  => 'Video',
                    'options' => [
                        'template' => [
                            'article'
                        ]
                    ],
                    'fields' => [
                        'caption' => [
                            'type' => 'text'
                        ],
                        'text' => [
                            'type' => 'textarea'
                        ]
                    ]
                ],
                'pages/article' => [
                    'title'  => 'Article',
                    'fields' => [
                        'text' => [
                            'type' => 'textarea'
                        ]
                    ]
                ]
            ],
            'hooks' => [
                'page.changeTemplate:before' => function (Page $page, $template) use ($phpunit, &$calls) {
                    $phpunit->assertSame('video', $page->intendedTemplate()->name());
                    $phpunit->assertSame('article', $template);
                    $calls++;
                },
                'page.changeTemplate:after' => function (Page $newPage, Page $oldPage) use ($phpunit, &$calls) {
                    $phpunit->assertSame('article', $newPage->intendedTemplate()->name());
                    $phpunit->assertSame('video', $oldPage->intendedTemplate()->name());
                    $calls++;
                }
            ]
        ]);

        $app->impersonate('kirby');

        $page = Page::create([
            'slug'     => 'test',
            'template' => 'video',
            'content'  => [
                'title'   => 'Test',
                'caption' => 'Caption',
                'text'    => 'Text'
            ]
        ]);

        $this->assertEquals('video', $page->intendedTemplate());

        $modified = $page->changeTemplate('article');

        $this->assertEquals('article', $modified->intendedTemplate());
        $this->assertSame(2, $calls);
    }

    public function testChangeTitle()
    {
        $page = Page::create([
            'slug' => 'test'
        ]);

        $this->assertEquals('test', $page->title());

        $modified = $page->changeTitle($title = 'Modified Title');

        $this->assertEquals($title, $modified->title());
    }

    public function testSave()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $this->assertFalse($page->exists());
        $page->save();
        $this->assertTrue($page->exists());
    }

    public function testUpdate()
    {
        $page = Page::create([
            'slug' => 'test'
        ]);

        $this->assertEquals(null, $page->headline()->value());

        $oldStatus = $page->status();
        $modified  = $page->update(['headline' => 'Test']);

        $this->assertEquals('Test', $modified->headline()->value());

        // assert that the page status didn't change with the update
        $this->assertEquals($oldStatus, $modified->status());
    }

    public function testUpdateHooks()
    {
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'page.update:before' => function (Page $page, $values, $strings) use ($phpunit) {
                    $phpunit->assertEquals('foo', $page->category());
                    $phpunit->assertEquals('foo', $page->siblings()->pluck('category')[0]->toString());
                    $phpunit->assertEquals('bar', $page->siblings()->pluck('category')[1]->toString());
                    $phpunit->assertEquals('foo', $page->parent()->children()->pluck('category')[0]->toString());
                    $phpunit->assertEquals('bar', $page->parent()->children()->pluck('category')[1]->toString());
                },
                'page.update:after' => function (Page $newPage, Page $oldPage) use ($phpunit) {
                    $phpunit->assertEquals('homer', $newPage->category());
                    $phpunit->assertEquals('homer', $newPage->siblings()->pluck('category')[0]->toString());
                    $phpunit->assertEquals('bar', $newPage->siblings()->pluck('category')[1]->toString());
                    $phpunit->assertEquals('homer', $newPage->parent()->children()->pluck('category')[0]->toString());
                    $phpunit->assertEquals('bar', $newPage->parent()->children()->pluck('category')[1]->toString());
                }
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'children' => [
                            [
                                'slug' => 'a',
                                'content' => [
                                    'category' => 'foo'
                                ]
                            ],
                            [
                                'slug' => 'b',
                                'content' => [
                                    'category' => 'bar'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');
        $app->page('test/a')->update(['category' => 'homer']);
    }

    public function languageProvider()
    {
        return [
            [null],
            ['en'],
            ['de']
        ];
    }

    /**
     * @dataProvider languageProvider
     */
    public function testUpdateMultilang($languageCode)
    {
        $app = $this->app->clone([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true
                ],
                [
                    'code' => 'de',
                    'name' => 'Deutsch'
                ]
            ]
        ]);

        $app->impersonate('kirby');

        if ($languageCode !== null) {
            $app->setCurrentLanguage($languageCode);
        }

        $page = Page::create([
            'slug' => 'test'
        ]);

        $this->assertEquals(null, $page->headline()->value());

        $modified = $page->update(['headline' => 'Test'], $languageCode);

        // check the modified response
        $this->assertEquals('Test', $modified->headline()->value());

        // also check in a freshly found page object
        $this->assertEquals('Test', $this->app->page('test')->headline()->value());
    }

    public function testUpdateMergeMultilang()
    {
        $app = $this->app->clone([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true
                ],
                [
                    'code' => 'de',
                    'name' => 'Deutsch'
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $page = Page::create([
            'slug' => 'test'
        ]);

        // add some content in both languages
        $page = $page->update([
            'a' => 'A (en)',
            'b' => 'B (en)'
        ], 'en');

        $page = $page->update([
            'a' => 'A (de)',
            'b' => 'B (de)'
        ], 'de');

        $this->assertEquals('A (en)', $page->content('en')->a());
        $this->assertEquals('B (en)', $page->content('en')->b());
        $this->assertEquals('A (de)', $page->content('de')->a());
        $this->assertEquals('B (de)', $page->content('de')->b());

        // update a single field in the primary language
        $page = $page->update([
            'b' => 'B modified (en)'
        ], 'en');

        $this->assertEquals('A (en)', $page->content('en')->a());
        $this->assertEquals('B modified (en)', $page->content('en')->b());

        // update a single field in the secondary language
        $page = $page->update([
            'b' => 'B modified (de)'
        ], 'de');

        $this->assertEquals('A (de)', $page->content('de')->a());
        $this->assertEquals('B modified (de)', $page->content('de')->b());
    }

    public function testChangeStatusListedHooks()
    {
        $phpunit = $this;
        $before  = 0;
        $after   = 0;

        $app = $this->app->clone([
            'hooks' => [
                'page.changeStatus:before' => function (Page $page, $status, $position) use (&$before, $phpunit) {
                    $phpunit->assertEquals('listed', $status);
                    $phpunit->assertEquals($before + 1, $position);
                    $before++;
                },
                'page.changeStatus:after' => function (Page $newPage, Page $oldPage) use (&$after, $phpunit) {
                    $phpunit->assertEquals('draft', $oldPage->status());
                    $phpunit->assertEquals('listed', $newPage->status());
                    $after++;
                }
            ]
        ]);

        $app->impersonate('kirby');

        $pageA = Page::create(['slug' => 'test-a', 'num' => null]);
        $pageB = Page::create(['slug' => 'test-b', 'num' => null]);

        $pageA->changeStatus('listed');
        $pageB->changeStatus('listed');

        $this->assertEquals(2, $before);
        $this->assertEquals(2, $after);
    }

    public function testChangeStatusUnlistedHooks()
    {
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'page.changeStatus:before' => function (Page $page, $status, $position) use ($phpunit) {
                    $phpunit->assertEquals('unlisted', $status);
                    $phpunit->assertNull($position);
                },
                'page.changeStatus:after' => function (Page $newPage, Page $oldPage) use ($phpunit) {
                    $phpunit->assertEquals('draft', $oldPage->status());
                    $phpunit->assertEquals('unlisted', $newPage->status());
                }
            ]
        ]);

        $app->impersonate('kirby');

        $page = Page::create(['slug' => 'test']);

        $page->changeStatus('unlisted');
    }

    public function testDuplicate()
    {
        $app = $this->app->clone();

        $app->impersonate('kirby');

        $page = Page::create([
            'slug' => 'test',
        ]);
        $page->lock()->create();
        $this->assertFileExists($app->locks()->file($page));

        $copy = $page->duplicate('test-copy');
        $this->assertFileDoesNotExist($this->fixtures . $copy->root() . '/.lock');
    }

    public function testDuplicateMultiLang()
    {
        $app = $this->app->clone([
            'languages' => [
                [
                    'code' => 'en',
                    'name' => 'English',
                    'default' => true
                ],
                [
                    'code' => 'de',
                    'name' => 'Deutsch',
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $page = Page::create([
            'slug' => 'test',
        ]);

        new ContentTranslation([
            'parent' => $page,
            'code'   => 'en',
        ]);
        $this->assertFileExists($page->contentFile('en'));

        $copy = $page->duplicate('test-copy');
        $this->assertFileExists($copy->contentFile('en'));
        $this->assertFileDoesNotExist($copy->contentFile('de'));
    }

    public function testChangeSlugHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'page.changeSlug:before' => function (Page $page, $slug, $languageCode) use ($phpunit, &$calls) {
                    $phpunit->assertSame('test', $page->slug());
                    $phpunit->assertSame('new-test', $slug);
                    $phpunit->assertNull($languageCode);
                    $calls++;
                },
                'page.changeSlug:after' => function (Page $newPage, Page $oldPage) use ($phpunit, &$calls) {
                    $phpunit->assertSame('new-test', $newPage->slug());
                    $phpunit->assertSame('test', $oldPage->slug());
                    $calls++;
                }
            ]
        ]);

        $app->impersonate('kirby');

        $page = new Page([
            'slug' => 'test'
        ]);

        $page->changeSlug('new-test');

        $this->assertSame(2, $calls);
    }

    public function testChangeTitleHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'page.changeTitle:before' => function (Page $page, $title, $languageCode) use ($phpunit, &$calls) {
                    $phpunit->assertSame('test', $page->title()->value);
                    $phpunit->assertSame('New Title', $title);
                    $phpunit->assertNull($languageCode);
                    $calls++;
                },
                'page.changeTitle:after' => function (Page $newPage, Page $oldPage) use ($phpunit, &$calls) {
                    $phpunit->assertSame('New Title', $newPage->title()->value());
                    $phpunit->assertSame('test', $oldPage->title()->value());
                    $calls++;
                }
            ]
        ]);

        $app->impersonate('kirby');

        $page = new Page([
            'slug' => 'test'
        ]);

        $page->changeTitle('New Title');

        $this->assertSame(2, $calls);
    }

    public function testCreateHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'page.create:before' => function (Page $page, $input) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\Page', $page);
                    $phpunit->assertSame([
                        'slug' => 'test',
                        'model' => 'default',
                        'template' => 'default',
                        'isDraft' => true
                    ], $input);
                    $calls++;
                },
                'page.create:after' => function (Page $page) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\Page', $page);
                    $phpunit->assertSame('test', $page->slug());
                    $calls++;
                }
            ]
        ]);

        $app->impersonate('kirby');

        Page::create([
            'slug' => 'test',
        ]);

        $this->assertSame(2, $calls);
    }

    public function testDeleteHooks()
    {
        $calls = 0;
        $phpunit  = $this;

        $app = $this->app->clone([
            'hooks' => [
                'page.delete:before' => function (Page $page, $force) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\Page', $page);
                    $phpunit->assertFalse($force);
                    $phpunit->assertFileExists($page->root());
                    $calls++;
                },
                'page.delete:after' => function ($status, Page $page) use ($phpunit, &$calls) {
                    $phpunit->assertTrue($status);
                    $phpunit->assertInstanceOf('Kirby\Cms\Page', $page);
                    $phpunit->assertFileDoesNotExist($page->root());
                    $calls++;
                }
            ]
        ]);

        $app->impersonate('kirby');

        $page = Page::create([
            'slug' => 'test'
        ]);

        $page->delete();

        $this->assertSame(2, $calls);
    }

    public function testDuplicateHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'page.duplicate:before' => function (Page $originalPage, $input, $options) use ($phpunit, &$calls) {
                    $phpunit->assertSame('test', $originalPage->slug());
                    $phpunit->assertSame('test-copy', $input);
                    $phpunit->assertSame([], $options);
                    $calls++;
                },
                'page.duplicate:after' => function (Page $duplicatePage, Page $originalPage) use ($phpunit, &$calls) {
                    $phpunit->assertSame('test-copy', $duplicatePage->slug());
                    $phpunit->assertSame('test', $originalPage->slug());
                    $calls++;
                }
            ]
        ]);

        $app->impersonate('kirby');

        $page = Page::create([
            'slug' => 'test'
        ]);

        $page->duplicate();

        $this->assertSame(2, $calls);
    }
}
