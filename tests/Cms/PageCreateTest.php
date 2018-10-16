<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\F;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PageCreateTest extends TestCase
{

    protected $app;
    protected $fixtures;

    public function setUp()
    {
        $this->app = new App([
            'roots' => [
               'index' => $this->fixtures = __DIR__ . '/fixtures/PageCreateTest'
            ]
        ]);

        $this->app->impersonate('kirby');

        Dir::make($this->fixtures);
    }

    public function tearDown()
    {
        Dir::remove($this->fixtures);
    }

    public function testCreateOnDisk()
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

    /**
     * @expectedException Kirby\Exception\DuplicateException
     */
    public function testCreateDuplicate()
    {
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
