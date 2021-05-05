<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
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

    public function testChangeTitleHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'site.changeTitle:before' => function (Site $site, $title, $languageCode) use ($phpunit, &$calls) {
                    $phpunit->assertNull($site->title()->value());
                    $phpunit->assertSame('New Title', $title);
                    $phpunit->assertNull($languageCode);
                    $calls++;
                },
                'site.changeTitle:after' => function (Site $newSite, Site $oldSite) use ($phpunit, &$calls) {
                    $phpunit->assertSame('New Title', $newSite->title()->value());
                    $phpunit->assertNull($oldSite->title()->value());
                    $calls++;
                }
            ]
        ]);

        $app->site()->changeTitle('New Title');

        $this->assertSame(2, $calls);
    }

    public function testUpdateHooks()
    {
        $calls = 0;
        $phpunit = $this;
        $input = [
            'copyright' => 'Kirby'
        ];

        $app = $this->app->clone([
            'hooks' => [
                'site.update:before' => function (Site $site, $values, $strings) use ($phpunit, $input, &$calls) {
                    $phpunit->assertNull($site->copyright()->value());
                    $phpunit->assertSame($input, $values);
                    $phpunit->assertSame($input, $strings);
                    $calls++;
                },
                'site.update:after' => function (Site $newSite, Site $oldSite) use ($phpunit, &$calls) {
                    $phpunit->assertSame('Kirby', $newSite->copyright()->value());
                    $phpunit->assertNull($oldSite->copyright()->value());
                    $calls++;
                }
            ]
        ]);

        $app->site()->update($input);

        $this->assertSame(2, $calls);
    }

    public function testPurge()
    {
        // we're going to test it on translations because it's just that public propery
        $app = $this->app->clone([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch'
                ]
            ],
            'site' => [
                'translations' => [
                    [
                        'code' => 'en',
                        'content' => [
                            'title' => 'Site',
                        ]
                    ],
                    [
                        'code' => 'de',
                        'content' => [
                            'title' => 'Seite',
                        ]
                    ],
                ]
            ]
        ]);

        $this->assertNotNull([], $app->site()->translations);
        $app->site()->purge();
        $this->assertNull($app->site()->translations);
    }
}
