<?php

namespace Kirby\Cms;

use Exception;

class PageActionsTestStore extends PageStoreDefault
{

    public static $exists = true;

    public function exists(): bool
    {
        return static::$exists;
    }

    public function delete(): bool
    {
        static::$exists = false;
        return true;
    }

}

class PageActionsTest extends TestCase
{

    public function setUp()
    {
        App::destroy();
    }

    public function pageDummy()
    {
        $parent = new Page(['slug' => 'parent']);

        $page = new Page([
            'slug'      => 'test',
            'template'  => 'test',
            'store'     => PageActionsTestStore::class,
            'parent'    => $parent
        ]);

        return $page->clone([
            'blueprint' => [
                'model' => $page,
                'title' => 'test',
                'name'  => 'test'
            ]
        ]);
    }

    public function testChangeSlug()
    {
        $this->assertHooks([
            'page.changeSlug:before' => function (Page $page, string $slug) {
                $this->assertEquals('test', $page->slug());
                $this->assertEquals('awesome', $slug);
            },
            'page.changeSlug:after' => function (Page $newPage, Page $oldPage) {
                $this->assertEquals('awesome', $newPage->slug());
                $this->assertEquals('test', $oldPage->slug());
            }
        ], function () {
            $result = $this->pageDummy()->changeSlug('awesome');
            $this->assertEquals('awesome', $result->slug());
        });
    }

    public function testChangeStatusToUnlisted()
    {
        $pageDummmy = $this->pageDummy()->clone([
            'num' => 1
        ]);

        $this->assertHooks([
            'page.changeStatus:before' => function (Page $page, string $status) {
                $this->assertEquals(1, $page->num());
                $this->assertEquals('unlisted', $status);
            },
            'page.changeStatus:after' => function (Page $newPage, Page $oldPage) {
                $this->assertEquals(null, $newPage->num());
                $this->assertEquals(1, $oldPage->num());
            }
        ], function () use ($pageDummmy) {
            $result = $pageDummmy->changeStatus('unlisted');
            $this->assertEquals(null, $result->num());
        });
    }

    public function testChangeSortToListed()
    {
        $this->assertHooks([
            'page.changeStatus:before' => function (Page $page, string $status, int $num = null) {
                $this->assertEquals(1, $num);
                $this->assertEquals('listed', $status);
            },
            'page.changeStatus:after' => function (Page $newPage, Page $oldPage) {
                $this->assertEquals(1, $newPage->num());
                $this->assertEquals(null, $oldPage->num());
            }
        ], function () {
            $result = $this->pageDummy()->changeStatus('listed', 1);
            $this->assertEquals(1, $result->num());
        });
    }

    public function testChangeTemplate()
    {
        $this->markTestIncomplete();

        $this->assertHooks([
            'page.changeTemplate:before' => function (Page $page, string $template) {
                $this->assertEquals('test', $page->template());
                $this->assertEquals('awesome', $template);
            },
            'page.changeTemplate:after' => function (Page $newPage, Page $oldPage) {
                $this->assertEquals('awesome', $newPage->template());
                $this->assertEquals('test', $oldPage->template());
            }
        ], function () {
            $result = $this->pageDummy()->changeTemplate('awesome');
            $this->assertEquals('awesome', $result->template());
        });
    }

    public function testChangeTitle()
    {
        $this->assertHooks([
            'page.changeTitle:before' => function (Page $page, string $title) {
                $this->assertEquals('test', $page->title()->value());
                $this->assertEquals('awesome', $title);
            },
            'page.changeTitle:after' => function (Page $newPage, Page $oldPage) {
                $this->assertEquals('awesome', $newPage->title()->value());
                $this->assertEquals('test', $oldPage->title()->value());
            }
        ], function () {
            $result = $this->pageDummy()->changeTitle('awesome');
            $this->assertEquals('awesome', $result->title()->value());
        });
    }

    public function testCreate()
    {
        $this->assertHooks([
            'page.create:before' => function (Page $parent = null, array $props) {
                $this->assertEquals('test', $props['slug']);
                $this->assertEquals('test', $props['template']);
            },
            'page.create:after' => function (Page $page) {
                $this->assertEquals('test', $page->slug());
                $this->assertEquals('test', $page->template());
            }
        ], function () {
            $page = Page::create([
                'slug'     => 'test',
                'template' => 'test',
                'site'     => new Site(),
                'store'    => PageActionsTestStore::class
            ]);
        });
    }

    public function testCreateChild()
    {
        $parent = $this->pageDummy();

        $this->assertHooks([
            'page.create:before' => function (Page $page, array $props) use ($parent) {
                $this->assertEquals('test-child', $props['slug']);
                $this->assertEquals($parent, $page->parent());
            },
            'page.create:after' => function (Page $page) {
                $this->assertEquals('test-child', $page->slug());
            }
        ], function () use ($parent) {
            $child = $parent->createChild([
                'slug' => 'test-child',
            ]);

            $this->assertEquals($child->parent(), $parent);
        });
    }

    public function testCreateFile()
    {
        new App([
            'users' => [
                ['email' => 'admin@getkirby.com', 'role' => 'admin']
            ],
            'user' => 'admin@getkirby.com'
        ]);

        $parent = $this->pageDummy();
        $file   = $parent->createFile([
            'source' => __DIR__ . '/fixtures/files/test.js'
        ]);

        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals($parent, $file->parent());
    }

    public function testDelete()
    {
        PageActionsTestStore::$exists = true;

        $this->assertHooks([
            'page.delete:before' => function (Page $page, bool $force) {
                $this->assertTrue($page->exists());
                $this->assertTrue($force);
            },
            'page.delete:after' => function (bool $result, Page $page) {
                $this->assertFalse($page->exists());
            }
        ], function () {
            $this->pageDummy()->delete($force = true);
        });
    }

    public function testUpdate()
    {
        $page = $this->pageDummy();
        $page = $page->clone([
            'blueprint' => [
                'name'   => 'test',
                'title'  => 'test',
                'model'  => $page,
                'fields' => [
                    'headline' => [
                        'type' => 'text'
                    ],
                    'text' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ]);

        $this->assertHooks([
            'page.update:before' => function (Page $page, array $values, array $strings) {
                $this->assertEquals(null, $page->headline()->value());
                $this->assertEquals(null, $page->text()->value());

                $this->assertEquals('Test', $strings['headline']);
                $this->assertEquals('Test', $strings['text']);
            },
            'page.update:after' => function (Page $newPage, Page $oldPage) {
                $this->assertEquals('Test', $newPage->headline()->value());
                $this->assertEquals(null, $oldPage->headline()->value());
            }
        ], function () use ($page) {
            $page->update([
                'headline' => 'Test',
                'text'     => 'Test'
            ]);
        });
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Nope
     */
    public function testUpdateException()
    {
        $app = new App([
            'hooks' => [
                'page.update:before' => function () {
                    throw new Exception('Nope');
                }
            ],
            'user' => 'test@getkirby.com',
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'admin']
            ]
        ]);

        $page = $this->pageDummy();
        $page = $page->clone([
            'blueprint' => [
                'name'   => 'test',
                'title'  => 'test',
                'model'  => $page,
                'fields' => [
                    'headline' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ]);

        $page->update(['headline' => 'test']);
    }

}
