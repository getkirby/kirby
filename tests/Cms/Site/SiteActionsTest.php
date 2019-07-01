<?php

namespace Kirby\Cms;

use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class SiteActionsTest extends TestCase
{
    protected $app;
    protected $fixtures = __DIR__ . '/fixtures/SiteActionsTest';

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures
            ],
            'users' => [
                [
                    'email' => 'admin@domain.com',
                    'role'  => 'admin'
                ]
            ],
            'user' => 'admin@domain.com',
            'blueprints' => [
                'site' => [
                    'name'   => 'site',
                    'title'  => 'Site',
                    'fields' => [
                        'copyright' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]);

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

    public function testChangeTitle()
    {
        $site = $this->site()->changeTitle('Test');
        $this->assertEquals('Test', $site->title()->value());
    }

    public function testCreateChild()
    {
        $page = $this->site()->createChild([
            'slug'     => 'test',
            'template' => 'test',
        ]);

        $this->assertEquals('test', $page->slug());
        $this->assertEquals('test', $page->intendedTemplate()->name());
    }

    public function testCreateFile()
    {
        F::write($source = $this->fixtures . '/source.md', '');

        $file = $this->site()->createFile([
            'filename' => 'test.md',
            'source'   => $source
        ]);

        $this->assertEquals('test.md', $file->filename());
    }

    public function testSave()
    {
        $site = $this->site()->clone(['content' => ['copyright' => 2012]])->save();
        $this->assertEquals(2012, $site->copyright()->value());
    }

    public function testUpdate()
    {
        $site = $this->site()->update([
            'copyright' => 2018
        ]);

        $this->assertEquals(2018, $site->copyright()->value());
    }
}
