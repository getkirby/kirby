<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\F;
use Kirby\Exception\InvalidArgumentException;
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

    public function testDeleteDraft()
    {
        $page = Page::create([
            'slug' => 'test'
        ]);

        $this->assertTrue($page->exists());
        $this->assertTrue($page->parentModel()->drafts()->has($page));

        $page->delete();

        $this->assertFalse($page->exists());
        $this->assertFalse($page->parentModel()->drafts()->has($page));
    }

    public function testDeletePage()
    {
        $page = Page::create([
            'slug' => 'test',
            'num'  => 1
        ]);

        $this->assertTrue($page->exists());
        $this->assertTrue($page->parentModel()->children()->has($page));

        $page->delete();

        $this->assertFalse($page->exists());
        $this->assertFalse($page->parentModel()->children()->has($page));
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
}
