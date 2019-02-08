<?php

namespace Kirby\Cms;

class RouterTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'home',
                    ],
                    [
                        'slug' => 'projects',
                        'children' => [
                            [
                                'slug'  => 'project-a',
                                'files' => [
                                    [
                                        'filename' => 'cover.jpg'
                                    ]
                                ]
                            ],
                        ],
                    ]
                ],
                'files' => [
                    [
                        'filename' => 'background.jpg'
                    ]
                ]
            ],
            'users' => [
                [
                    'id'    => 'test',
                    'email' => 'admin@getkirby.com',
                    'files' => [
                        [
                            'filename' => 'test.jpg'
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testHomeRoute()
    {
        $page = $this->app->call('');
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
    }

    public function testHomeFolderRoute()
    {
        $response = $this->app->call('home');
        $this->assertInstanceOf(Responder::class, $response);
        $this->assertEquals(302, $response->code());
    }

    public function testHomeCustomFolderRoute()
    {
        $app = $this->app->clone([
            'options' => [
                'home' => 'homie'
            ],
            'children' => [
                [
                    'slug' => 'homie'
                ]
            ]
        ]);

        $response = $app->call('homie');
        $this->assertInstanceOf(Responder::class, $response);
        $this->assertEquals(302, $response->code());
    }

    public function testPageRoute()
    {
        $page = $this->app->call('projects');
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('projects', $page->id());
    }

    public function testPageFileRoute()
    {
        $file = $this->app->call('projects/project-a/cover.jpg');
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('projects/project-a/cover.jpg', $file->id());
    }

    public function testSiteFileRoute()
    {
        $file = $this->app->call('background.jpg');
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('background.jpg', $file->id());
    }

    public function testNestedPageRoute()
    {
        $page = $this->app->call('projects/project-a');
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('projects/project-a', $page->id());
    }

    public function testNotFoundRoute()
    {
        $page = $this->app->call('not-found');
        $this->assertNull($page);
    }

    public function testPageMediaRoute()
    {
        $response = $this->app->call('media/pages/projects/project-a/1234-5678/cover.jpg');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSiteMediaRoute()
    {
        $response = $this->app->call('media/site/1234-5678/background.jpg');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testUserMediaRoute()
    {
        $response = $this->app->call('media/users/test/1234-5678/test.jpg');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testDisabledApi()
    {
        $app = $this->app->clone([
            'options' => [
                'api' => false
            ]
        ]);

        $this->assertNull($app->call('api'));
        $this->assertNull($app->call('api/something'));
    }

    public function testDisabledPanel()
    {
        $app = $this->app->clone([
            'options' => [
                'panel' => false
            ]
        ]);

        $this->assertNull($app->call('panel'));
        $this->assertNull($app->call('panel/something'));
    }

    public function customRouteProvider()
    {
        return [
            // home
            ['/', ''],
            ['/', '/'],
            ['', ''],
            ['', '/'],

            // main page
            ['(:any)', 'test'],
            ['(:any)', '/test'],
            ['/(:any)', 'test'],
            ['/(:any)', '/test'],

            // subpages
            ['(:all)', 'foo/bar'],
            ['(:all)', '/foo/bar'],
            ['/(:all)', 'foo/bar'],
            ['/(:all)', '/foo/bar'],
        ];
    }

    /**
     * @dataProvider customRouteProvider
     */
    public function testCustomRoute($pattern, $path)
    {
        $app = $this->app->clone([
            'routes' => [
                [
                    'pattern' => $pattern,
                    'action'  => function () {
                        return 'test';
                    }
                ]
            ]
        ]);

        $this->assertEquals('test', $app->call($path));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid routing method: WURST
     * @expectedExceptionCode 400
     */
    public function testBadMethodRoute()
    {
        $this->app->call('/', 'WURST');
    }
}
