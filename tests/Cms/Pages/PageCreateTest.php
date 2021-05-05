<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class UncreatablePage extends Page
{
    public static function create(array $props)
    {
        return 'the model was used';
    }
}

class PageCreateTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/PageCreateTest'
            ]
        ]);

        $this->app->impersonate('kirby');

        Dir::make($this->fixtures);

        Page::$models = [
            'uncreatable-page' => UncreatablePage::class
        ];
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);

        Page::$models = [];
    }

    public function testCreateDraft()
    {
        $site = $this->app->site();
        $page = Page::create([
            'slug' => 'new-page',
        ]);

        $this->assertTrue($page->exists());
        $this->assertInstanceOf(Page::class, $page);
        $this->assertTrue($page->isDraft());
        $this->assertTrue($page->parentModel()->drafts()->has($page));
        $this->assertTrue($site->drafts()->has($page));
    }

    public function testCreateDraftWithDefaults()
    {
        $site = $this->app->site();
        $page = Page::create([
            'slug' => 'new-page',
            'blueprint' => [
                'name'   => 'test',
                'fields' => [
                    'a'  => [
                        'type'    => 'text',
                        'default' => 'A'
                    ],
                    'b' => [
                        'type'    => 'textarea',
                        'default' => 'B'
                    ]
                ]
            ]
        ]);

        $this->assertEquals('A', $page->a()->value());
        $this->assertEquals('B', $page->b()->value());
    }

    public function testCreateDraftWithDefaultsAndContent()
    {
        $site = $this->app->site();
        $page = Page::create([
            'content' => [
                'a' => 'Custom A'
            ],
            'slug' => 'new-page',
            'blueprint' => [
                'name'   => 'test',
                'fields' => [
                    'a'  => [
                        'type'    => 'text',
                        'default' => 'A'
                    ],
                    'b' => [
                        'type'    => 'textarea',
                        'default' => 'B'
                    ]
                ]
            ]
        ]);

        $this->assertEquals('Custom A', $page->a()->value());
        $this->assertEquals('B', $page->b()->value());
    }

    public function testCreateListedPage()
    {
        $site = $this->app->site();
        $page = Page::create([
            'slug' => 'new-page',
            'num'  => 1
        ]);

        $this->assertTrue($page->exists());
        $this->assertInstanceOf(Page::class, $page);
        $this->assertFalse($page->isDraft());
        $this->assertTrue($page->parentModel()->children()->has($page));
        $this->assertTrue($site->children()->has($page));
    }

    public function testCreateDuplicate()
    {
        $this->expectException('Kirby\Exception\DuplicateException');

        $page = Page::create([
            'slug' => 'new-page',
        ]);

        $page = Page::create([
            'slug' => 'new-page',
        ]);
    }

    public function testCreateChild()
    {
        Dir::make($this->app->root('content'));

        $mother = Page::create([
            'slug' => 'mother'
        ]);

        $child = $mother->createChild([
            'slug'     => 'child',
            'template' => 'the-template'
        ]);

        $this->assertTrue($child->exists());
        $this->assertEquals('the-template', $child->intendedTemplate()->name());
        $this->assertEquals('child', $child->slug());
        $this->assertEquals('mother/child', $child->id());
        $this->assertTrue($mother->drafts()->has($child->id()));
    }

    public function testCreateChildCustomModel()
    {
        $mother = Page::create([
            'slug' => 'mother'
        ]);

        $child = $mother->createChild([
            'slug'     => 'child',
            'template' => 'uncreatable-page'
        ]);

        $this->assertSame('the model was used', $child);
        $this->assertTrue($mother->drafts()->isEmpty());
    }

    public function testCreateFile()
    {
        F::write($source = $this->fixtures . '/source.md', '');

        $page = Page::create([
            'slug' => 'test'
        ]);

        $file = $page->createFile([
            'filename' => 'test.md',
            'source'   => $source
        ]);

        $this->assertEquals('test.md', $file->filename());
        $this->assertEquals('test/test.md', $file->id());
    }
}
