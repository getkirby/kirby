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

    public function app()
    {
        return new App([
            'blueprints' => [
                'pages/default' => [
                    'title'  => 'Default',
                    'name'   => 'default',
                    'fields' => [
                        'headline' => [
                            'type' => 'text'
                        ]
                    ]
                ],
                'pages/article' => [
                    'title'  => 'Article',
                    'name'   => 'article',
                    'num'    => 'date',
                    'status' => ['draft' => 'Draft', 'listed' => 'Published'],
                    'fields' => [
                        'date' => [
                            'type' => 'date'
                        ]
                    ]
                ]
            ],
            'roots' => [
               'index' => $this->fixtures = __DIR__ . '/fixtures/PageActionsTest'
            ],
            'site' => [
                'children' => [
                    [
                        'slug'  => 'test',
                    ],
                    [
                        'slug'     => 'article',
                        'num'      => 20121212,
                        'template' => 'article'
                    ]
                ],
            ],
            'users' => [
                [
                    'email' => 'admin@domain.com',
                    'role'  => 'admin'
                ]
            ],
            'user' => 'admin@domain.com'
        ]);
    }

    public function setUp()
    {
        $this->app = $this->app();
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
        $page = $this->site()->find('test')->save();

        $this->assertTrue($page->exists());
        $this->assertEquals('test', $page->slug());

        $modified = $page->changeSlug('modified-test');

        $this->assertTrue($modified->exists());
        $this->assertEquals('modified-test', $modified->slug());
    }

    public function testChangeStatus()
    {
        $page = $this->site()->find('test')->save();
        $this->assertEquals('unlisted', $page->status());

        $listed = $page->changeStatus('listed');
        $this->assertEquals('listed', $listed->status());

        $unlisted = $page->changeStatus('unlisted');
        $this->assertEquals('unlisted', $unlisted->status());

        $draft = $page->changeStatus('draft');
        $this->assertEquals('draft', $draft->status());
    }

    public function testChangeStatusToInvalidStatus()
    {
        $page = $this->site()->find('article')->save();
        $this->assertEquals('listed', $page->status());

        $draft = $page->changeStatus('draft');
        $this->assertEquals('draft', $draft->status());

        $this->expectException(InvalidArgumentException::class);

        $unlisted = $page->changeStatus('unlisted');
        $this->assertEquals('unlisted', $unlisted->status());
    }

    public function testChangeTemplate()
    {

    }

    public function testChangeTitle()
    {
        $page = $this->site()->find('test');
        $this->assertEquals('test', $page->title());

        $modified = $page->changeTitle($title = 'Modified Title');
        $this->assertEquals($title, $modified->title());
    }

    public function testCreate()
    {
        $page = Page::create([
            'slug' => 'new-page',
        ]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertTrue($page->exists());
        $this->assertTrue($page->isDraft());
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
        $page    = $this->site()->find('test')->save();
        $subpage = $page->createChild([
            'slug'     => 'subpage',
            'template' => 'the-template'
        ]);

        $this->assertTrue($subpage->exists());
        $this->assertEquals('the-template', $subpage->intendedTemplate()->name());
        $this->assertEquals('subpage', $subpage->slug());
        $this->assertEquals('test/subpage', $subpage->id());
    }

    public function testCreateFile()
    {
        F::write($source = $this->fixtures . '/source.md', '');

        $file = $this->site()->find('test')->createFile([
            'filename' => 'test.md',
            'source'   => $source
        ]);

        $this->assertEquals('test.md', $file->filename());
    }

    public function testCreateNum()
    {

    }

    public function testDelete()
    {
        $page = $this->site()->find('test')->save();
        $this->assertTrue($page->exists());

        $page->delete();
        $this->assertFalse($page->exists());
    }

    public function testPublish()
    {
        $page = Page::create([
            'slug' => 'new-page',
        ]);

        $published = $page->publish();
        $this->assertEquals('unlisted', $published->status());
    }

    public function testSave()
    {
        $page = $this->site()->find('test');

        $this->assertFalse($page->exists());
        $page->save();
        $this->assertTrue($page->exists());
    }

    public function testUpdate()
    {
        $page = $this->site()->find('test')->save();
        $this->assertEquals(null, $page->headline()->value());

        $oldStatus = $page->status();

        $modified = $page->update(['headline' => 'Test']);
        $this->assertEquals('Test', $modified->headline()->value());

        // assert that the page status didn't change with the update
        $this->assertEquals($oldStatus, $modified->status());
    }

    public function testUpdateWithDateBasedNumbering()
    {

        $page = $this->site()->find('article')->save();

        $this->assertEquals(20121212, $page->num());

        $modified = $page->update([
            'date' => '2018-12-12'
        ]);

        $this->assertEquals(20181212, $modified->num());

    }

}
