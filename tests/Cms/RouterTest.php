<?php

namespace Kirby\Cms;

use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;

class RouterTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->fixtures = __DIR__ . '/fixtures/RouterTest';

        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testHomeRoute()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'home']
                ]
            ]
        ]);

        $page = $app->call('');
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
    }

    public function testHomeFolderRoute()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'home']
                ]
            ]
        ]);

        $response = $app->call('home');
        $this->assertInstanceOf(Responder::class, $response);
        $this->assertEquals(302, $response->code());
    }

    public function testHomeCustomFolderRoute()
    {
        $app = $this->app->clone([
            'options' => [
                'home' => 'homie'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'homie'
                    ]
                ]
            ]
        ]);

        $response = $app->call('homie');
        $this->assertInstanceOf(Responder::class, $response);
        $this->assertEquals(302, $response->code());
    }

    public function testHomeRouteWithoutHomePage()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => []
            ]
        ]);

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The home page does not exist');

        $app->call('/');
    }

    public function testPageRoute()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'projects'
                    ]
                ]
            ]
        ]);

        $page = $app->call('projects');
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('projects', $page->id());
    }

    public function testPageRepresentationRoute()
    {
        F::write($template = $this->fixtures . '/test.php', 'html');
        F::write($template = $this->fixtures . '/test.xml.php', 'xml');

        $app = new App([
            'roots' => [
                'index'     => '/dev/null',
                'templates' => $this->fixtures
            ],
            'site' => [
                'children' => [
                    [
                        'slug'     => 'test',
                        'template' => 'test'
                    ]
                ],
            ]
        ]);

        // missing representation
        $result = $app->call('test.json');
        $this->assertNull($result);

        // xml presentation
        $result = $app->call('test.xml');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
    }

    public function testPageFileRoute()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug'  => 'projects',
                        'files' => [
                            [
                                'filename' => 'cover.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $file = $app->call('projects/cover.jpg');
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('projects/cover.jpg', $file->id());
    }

    public function testSiteFileRoute()
    {
        $app = $this->app->clone([
            'site' => [
                'files' => [
                    [
                        'filename' => 'background.jpg'
                    ]
                ]
            ]
        ]);

        $file = $app->call('background.jpg');
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('background.jpg', $file->id());
    }

    public function testNestedPageRoute()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug'  => 'projects',
                        'children' => [
                            [
                                'slug' => 'project-a'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page = $app->call('projects/project-a');
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
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug'  => 'projects',
                        'files' => [
                            [
                                'filename' => 'cover.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $response = $app->call('media/pages/projects/1234-5678/cover.jpg');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSiteMediaRoute()
    {
        $app = $this->app->clone([
            'site' => [
                'files' => [
                    [
                        'filename' => 'background.jpg'
                    ]
                ]
            ]
        ]);

        $response = $app->call('media/site/1234-5678/background.jpg');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testUserMediaRoute()
    {
        $app = $this->app->clone([
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

        $response = $app->call('media/users/test/1234-5678/test.jpg');
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

    public function testBadMethodRoute()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid routing method: WURST');
        $this->expectExceptionCode(400);

        $this->app->call('/', 'WURST');
    }

    public function testMultiLangHomeRoute()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'home'
                    ]
                ]
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'en',
                ],
                [
                    'code' => 'de',
                    'default' => true
                ]
            ]
        ]);


        // de
        $page = $app->call('de');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
        $this->assertEquals('de', $app->language()->code());
        $this->assertEquals('de', I18n::locale());

        // en
        $page = $app->call('en');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
        $this->assertEquals('en', $app->language()->code());
        $this->assertEquals('en', I18n::locale());

        // redirect
        $result = $app->call('/');

        $this->assertInstanceOf(Responder::class, $result);
    }

    public function testMultiLangHomeRouteWithoutLanguageCode()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'home'
                    ]
                ]
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'de',
                    'default' => true,
                    'url'     => '/'
                ],
                [
                    'code' => 'en',
                ]
            ]
        ]);

        // de
        $page = $app->call('/');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
        $this->assertEquals('de', $app->language()->code());
        $this->assertEquals('de', I18n::locale());

        // en
        $page = $app->call('en');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
        $this->assertEquals('en', $app->language()->code());
        $this->assertEquals('en', I18n::locale());
    }

    public function testMultiLangHomeRouteWithoutHomePage()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => []
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'de',
                    'default' => true,
                    'url'     => '/'
                ],
                [
                    'code' => 'en',
                ]
            ]
        ]);

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The home page does not exist');

        $app->call('/');
    }

    public function testMultiLangPageRoute()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'projects'
                    ]
                ]
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'de',
                    'default' => true
                ],
                [
                    'code' => 'en',
                ]
            ]
        ]);

        // en
        $page = $app->call('en/projects');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('projects', $page->id());
        $this->assertEquals('en', $app->language()->code());
        $this->assertEquals('en', I18n::locale());

        // de
        $page = $app->call('de/projects');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('projects', $page->id());
        $this->assertEquals('de', $app->language()->code());
        $this->assertEquals('de', I18n::locale());
    }

    public function testMultilangPageRepresentationRoute()
    {
        F::write($template = $this->fixtures . '/test.php', 'html');
        F::write($template = $this->fixtures . '/test.xml.php', 'xml');

        $app = new App([
            'roots' => [
                'index'     => '/dev/null',
                'templates' => $this->fixtures
            ],
            'site' => [
                'children' => [
                    [
                        'slug'     => 'test',
                        'template' => 'test'
                    ]
                ],
            ],
            'languages' => [
                [
                    'code'    => 'de',
                    'default' => true
                ],
                [
                    'code' => 'en',
                ]
            ]
        ]);

        /* DE */

        // missing representation
        $result = $app->call('de/test.json');
        $this->assertNull($result);

        // xml presentation
        $result = $app->call('de/test.xml');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
        $this->assertEquals('de', $app->language()->code());
        $this->assertEquals('de', I18n::locale());

        /* EN */

        // missing representation
        $result = $app->call('en/test.json');
        $this->assertNull($result);

        // xml presentation
        $result = $app->call('en/test.xml');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
        $this->assertEquals('en', $app->language()->code());
        $this->assertEquals('en', I18n::locale());
    }

    public function testMultilangPageRepresentationRouteWithoutLanguageCode()
    {
        F::write($template = $this->fixtures . '/test.php', 'html');
        F::write($template = $this->fixtures . '/test.xml.php', 'xml');

        $app = new App([
            'roots' => [
                'index'     => '/dev/null',
                'templates' => $this->fixtures
            ],
            'site' => [
                'children' => [
                    [
                        'slug'     => 'test',
                        'template' => 'test'
                    ]
                ],
            ],
            'languages' => [
                [
                    'code'    => 'de',
                    'default' => true,
                    'url'     => '/'
                ],
                [
                    'code' => 'en',
                ]
            ]
        ]);

        /* DE */

        // missing representation
        $result = $app->call('test.json');
        $this->assertNull($result);

        // xml presentation
        $result = $app->call('test.xml');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
        $this->assertEquals('de', $app->language()->code());
        $this->assertEquals('de', I18n::locale());

        /* EN */

        // missing representation
        $result = $app->call('en/test.json');
        $this->assertNull($result);

        // xml presentation
        $result = $app->call('en/test.xml');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
        $this->assertEquals('en', $app->language()->code());
        $this->assertEquals('en', I18n::locale());
    }
}
