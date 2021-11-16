<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Route;
use Kirby\Toolkit\I18n;

class RouterTest extends TestCase
{
    protected $app;
    protected $fixtures;

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

        $mediaHash = $app->file('projects/cover.jpg')->mediaHash();

        $response = $app->call('media/pages/projects/' . $mediaHash . '/cover.jpg');
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

        $mediaHash = $app->file('background.jpg')->mediaHash();

        $response = $app->call('media/site/' . $mediaHash . '/background.jpg');
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

        $mediaHash = $app->user('test')->file('test.jpg')->mediaHash();

        $response = $app->call('media/users/test/' . $mediaHash . '/test.jpg');
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

        // the api route should still be there
        $patterns = array_column($app->routes(), 'pattern');
        $this->assertEquals('api/(:all)', $patterns[0]);
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
                    'code' => 'fr',
                    'default' => true
                ]
            ]
        ]);


        // fr
        $page = $app->call('fr');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
        $this->assertEquals('fr', $app->language()->code());
        $this->assertEquals('fr', I18n::locale());

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
                    'code'    => 'fr',
                    'default' => true,
                    'url'     => '/'
                ],
                [
                    'code' => 'en',
                    'url'  => '/en'
                ],
            ]
        ]);

        // fr
        $page = $app->call('/');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
        $this->assertEquals('fr', $app->language()->code());
        $this->assertEquals('fr', I18n::locale());

        // en
        $page = $app->call('en');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
        $this->assertEquals('en', $app->language()->code());
        $this->assertEquals('en', I18n::locale());
    }

    public function multiDomainProvider()
    {
        return [
            ['https://getkirby.fr', 'fr'],
            ['https://getkirby.com', 'en'],
        ];
    }

    /**
     * @dataProvider multiDomainProvider
     */
    public function testMultiLangHomeWithDifferentDomains($domain, $language)
    {
        $app = $this->app->clone([
            'urls' => [
                'index' => $domain
            ],
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
                    'code'    => 'fr',
                    'default' => true,
                    'url'     => 'https://getkirby.fr'
                ],
                [
                    'code' => 'en',
                    'url'  => 'https://getkirby.com'
                ]
            ]
        ]);

        // home
        $page = $app->call('');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
        $this->assertEquals($language, $app->language()->code());
        $this->assertEquals($language, I18n::locale());
    }

    /**
     * @dataProvider multiDomainProvider
     */
    public function testMultiLangHomeWithDifferentDomainsAndPath($domain, $language)
    {
        $app = $this->app->clone([
            'urls' => [
                'index' => $domain
            ],
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
                    'code'    => 'fr',
                    'default' => true,
                    'url'     => 'https://getkirby.fr/subfolder'
                ],
                [
                    'code' => 'en',
                    'url'  => 'https://getkirby.com/subfolder'
                ]
            ]
        ]);

        // redirect
        $redirect = $app->call('');
        $this->assertInstanceOf(Responder::class, $redirect);

        // home
        $page = $app->call('subfolder');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('home', $page->id());
        $this->assertEquals($language, $app->language()->code());
        $this->assertEquals($language, I18n::locale());
    }

    public function acceptedLanguageProvider()
    {
        return [
            ['fr,en;q=0.8', '/fr'],
            ['en', '/en'],
            ['de', '/fr']
        ];
    }

    /**
     * @dataProvider acceptedLanguageProvider
     */
    public function testMultiLangHomeRouteWithoutLanguageCodeAndLanguageDetection($accept, $redirect)
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
                'languages' => true,
                'languages.detect' => true
            ],
            'languages' => [
                [
                    'code'    => 'fr',
                    'default' => true,
                ],
                [
                    'code' => 'en',
                ]
            ]
        ]);

        $acceptedLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;

        // set the accepted visitor language
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $accept;
        $app = $app->clone();

        $response = $app->call('/');

        $this->assertInstanceOf(Responder::class, $response);
        $this->assertEquals(['Location' => $redirect], $response->headers());

        // reset the accepted visitor language
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $acceptedLanguage;
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
                    'code'    => 'fr',
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
                    'code'    => 'fr',
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

        // fr
        $page = $app->call('fr/projects');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('projects', $page->id());
        $this->assertEquals('fr', $app->language()->code());
        $this->assertEquals('fr', I18n::locale());
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
                    'code'    => 'fr',
                    'default' => true
                ],
                [
                    'code' => 'en',
                ]
            ]
        ]);

        // DE

        // missing representation
        $result = $app->call('fr/test.json');
        $this->assertNull($result);

        // xml presentation
        $result = $app->call('fr/test.xml');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
        $this->assertEquals('fr', $app->language()->code());
        $this->assertEquals('fr', I18n::locale());

        // EN

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
                    'code'    => 'fr',
                    'default' => true,
                    'url'     => '/'
                ],
                [
                    'code' => 'en',
                ]
            ]
        ]);

        // FR

        // missing representation
        $result = $app->call('test.json');
        $this->assertNull($result);

        // xml presentation
        $result = $app->call('test.xml');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
        $this->assertEquals('fr', $app->language()->code());
        $this->assertEquals('fr', I18n::locale());

        // EN

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

    public function testCustomMediaFolder()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com',
                'media' => $media = 'https://getkirby.com/thumbs'
            ]
        ]);

        $this->assertEquals($media, $app->url('media'));

        // call custom media route
        $route = $app->router()->find('thumbs/pages/a/b/1234-5678/test.jpg', 'GET');
        $this->assertStringContainsString('thumbs/pages/(.*)', $route->pattern());

        $route = $app->router()->find('thumbs/site/1234-5678/test.jpg', 'GET');
        $this->assertStringContainsString('thumbs/site/([a-z', $route->pattern());

        $route = $app->router()->find('thumbs/users/test@getkirby.com/1234-5678/test.jpg', 'GET');
        $this->assertStringContainsString('thumbs/users/([a-z', $route->pattern());

        // default media route should result in the fallback route
        $route = $app->router()->find('media/pages/a/b/1234-5678/test.jpg', 'GET');
        $this->assertEquals('(.*)', $route->pattern());

        $route = $app->router()->find('media/site/1234-5678/test.jpg', 'GET');
        $this->assertEquals('(.*)', $route->pattern());

        $route = $app->router()->find('media/users/test@getkirby.com/1234-5678/test.jpg', 'GET');
        $this->assertEquals('(.*)', $route->pattern());
    }
}
