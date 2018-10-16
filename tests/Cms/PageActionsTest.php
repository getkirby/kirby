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

    public function setUp()
    {
        $this->app = new App([
            'roots' => [
               'index' => $this->fixtures = __DIR__ . '/fixtures/PageActionsTest'
            ],
        ]);

        $this->app->impersonate('kirby');

        Dir::make($this->fixtures);
    }

    public function tearDown()
    {
        Dir::remove($this->fixtures);
    }

    public function site()
    {
        return $this->app->site();
    }

    public function testChangeSlug()
    {
        $page = Page::create([
            'slug' => 'test'
        ]);

        $this->assertTrue($page->exists());
        $this->assertEquals('test', $page->slug());

        $modified = $page->changeSlug('modified-test');

        $this->assertTrue($modified->exists());
        $this->assertEquals('modified-test', $modified->slug());
    }

    public function testChangeTemplate()
    {

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

}
