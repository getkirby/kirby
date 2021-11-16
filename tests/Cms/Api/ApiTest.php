<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Toolkit\I18n;

class ApiTest extends TestCase
{
    protected $api;
    protected $locale;
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/ApiTest'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'children' => [
                            [
                                'slug' => 'aa'
                            ],
                            [
                                'slug' => 'ab'
                            ]
                        ],
                        'files' => [
                            [
                                'filename' => 'a-regular-file.jpg',
                            ],
                            [
                                'filename' => 'a filename with spaces.jpg',
                            ]
                        ]
                    ],
                    [
                        'slug' => 'b'
                    ]
                ]
            ],
            'options' => [
                'api' => [
                    'allowImpersonation' => true,
                    'authentication' => function () {
                        return true;
                    },
                    'routes' => [
                        [
                            'pattern' => 'foo',
                            'method'  => 'GET',
                            'action'  => function () {
                                return 'something';
                            }
                        ]
                    ]
                ],
                'locale' => 'de_DE.UTF-8'
            ],
        ]);

        $this->app->impersonate('kirby');
        $this->api = $this->app->api();

        $this->locale = setlocale(LC_ALL, 0);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
        setlocale(LC_ALL, $this->locale);
    }

    public function testCallLocaleSingleLang1()
    {
        setlocale(LC_ALL, 'C');
        $this->assertSame('C', setlocale(LC_ALL, 0));

        $this->assertSame('something', $this->api->call('foo'));
        $this->assertSame('de_DE.UTF-8', setlocale(LC_ALL, 0));
    }

    public function testCallLocaleSingleLang2()
    {
        setlocale(LC_ALL, 'C');
        $this->assertSame('C', setlocale(LC_ALL, 0));

        $_GET['language'] = 'en';

        $this->assertSame('something', $this->api->call('foo'));
        $this->assertSame('de_DE.UTF-8', setlocale(LC_ALL, 0));

        $_GET = [];
    }

    public function testCallLocaleMultiLang1()
    {
        setlocale(LC_ALL, 'C');
        $this->assertSame('C', setlocale(LC_ALL, 0));

        $this->app = $this->app->clone([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true,
                    'locale'  => 'en_US.UTF-8',
                    'url'     => '/',
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch',
                    'locale'  => 'de_AT.UTF-8',
                    'url'     => '/de',
                ],
            ]
        ]);
        $this->api = $this->app->api();

        $this->assertSame('something', $this->api->call('foo'));
        $this->assertSame('en_US.UTF-8', setlocale(LC_ALL, 0));
    }

    public function testCallLocaleMultiLang2()
    {
        setlocale(LC_ALL, 'C');
        $this->assertSame('C', setlocale(LC_ALL, 0));

        $this->app = $this->app->clone([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true,
                    'locale'  => 'en_US.UTF-8',
                    'url'     => '/',
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch',
                    'locale'  => 'de_AT.UTF-8',
                    'url'     => '/de',
                ],
            ]
        ]);
        $this->api = $this->app->api();

        $_GET['language'] = 'de';

        $this->assertSame('something', $this->api->call('foo'));
        $this->assertSame('de_AT.UTF-8', setlocale(LC_ALL, 0));

        $_GET = [];
    }

    public function testCallTranslation()
    {
        // with logged in user with language
        $app = $this->app->clone([
            'users' => [
                [
                    'email'    => 'homer@simpsons.com',
                    'language' => 'fr'
                ]
            ]
        ]);
        $app->impersonate('homer@simpsons.com');

        $api = $app->api();
        $this->assertSame('something', $api->call('foo'));
        $this->assertSame('fr', I18n::$locale);

        // with logged in user without language
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'homer@simpsons.com'
                ]
            ],
            'languages' => [
                [
                    'code'    => 'it-it',
                    'default' => true,
                ]
            ],
            'options' => [
                'panel.language' => 'de'
            ]
        ]);
        $app->impersonate('homer@simpsons.com');

        $api = $app->api();
        $this->assertSame('something', $api->call('foo'));
        $this->assertSame('de', I18n::$locale);

        // with logged in user without language without Panel language
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'homer@simpsons.com'
                ]
            ],
            'languages' => [
                [
                    'code'    => 'it-it',
                    'default' => true,
                ]
            ]
        ]);
        $app->impersonate('homer@simpsons.com');

        $api = $app->api();
        $this->assertSame('something', $api->call('foo'));
        $this->assertSame('it', I18n::$locale);

        // with logged in user without any configuration
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'homer@simpsons.com'
                ]
            ]
        ]);
        $app->impersonate('homer@simpsons.com');

        $api = $app->api();
        $this->assertSame('something', $api->call('foo'));
        $this->assertSame('en', I18n::$locale);

        // without logged in user
        $app = $this->app->clone([
            'languages' => [
                [
                    'code'    => 'it-it',
                    'default' => true,
                ]
            ],
            'options' => [
                'panel.language' => 'de'
            ]
        ]);

        $api = $app->api();
        $this->assertSame('something', $api->call('foo'));
        $this->assertSame('de', I18n::$locale);

        // without logged in user without Panel language
        $app = $this->app->clone([
            'languages' => [
                [
                    'code'    => 'it-it',
                    'default' => true,
                ]
            ]
        ]);

        $api = $app->api();
        $this->assertSame('something', $api->call('foo'));
        $this->assertSame('it', I18n::$locale);

        // without logged in user without any configuration
        $app = $this->app->clone();
        $api = $app->api();
        $this->assertSame('something', $api->call('foo'));
        $this->assertSame('en', I18n::$locale);
    }

    public function testLanguage()
    {
        $api = $this->api->clone([
            'requestData' => [
                'headers' => [
                    'x-language' => 'de'
                ]
            ]
        ]);

        $this->assertEquals('de', $api->language());
    }

    public function testFile()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug'  => 'a',
                        'files' => [
                            ['filename' => 'test.jpg']
                        ],
                        'children' => [
                            [
                                'slug' => 'a',
                                'files' => [
                                    ['filename' => 'test.jpg']
                                ],
                            ]
                        ]
                    ]
                ],
                'files' => [
                    ['filename' => 'test.jpg']
                ]
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'files' => [
                        ['filename' => 'test.jpg']
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');
        $api = $app->api();

        $this->assertEquals('test.jpg', $api->file('site', 'test.jpg')->filename());
        $this->assertEquals('test.jpg', $api->file('pages/a', 'test.jpg')->filename());
        $this->assertEquals('test.jpg', $api->file('pages/a+a', 'test.jpg')->filename());
        $this->assertEquals('test.jpg', $api->file('users/test@getkirby.com', 'test.jpg')->filename());
    }

    public function testFileNotFound()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The file "nope.jpg" cannot be found');

        $this->api->file('site', 'nope.jpg');
    }

    public function testFileNotReadable()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The file "protected.jpg" cannot be found');

        $app = $this->app->clone([
            'blueprints' => [
                'files/protected' => [
                    'options' => ['read' => false]
                ]
            ],
            'site' => [
                'files' => [
                    ['filename' => 'protected.jpg', 'template' => 'protected']
                ]
            ]
        ]);

        $this->api->file('site', 'protected.jpg');
    }

    public function testPage()
    {
        $a  = $this->app->page('a');
        $aa = $this->app->page('a/aa');

        $this->assertEquals($a, $this->api->page('a'));
        $this->assertEquals($aa, $this->api->page('a+aa'));

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The page "does-not-exist" cannot be found');
        $this->api->page('does-not-exist');
    }

    public function testUser()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'current@getkirby.com',
                ],
                [
                    'email' => 'test@getkirby.com',
                ]
            ],
        ]);

        $app->impersonate('current@getkirby.com');
        $api = $app->api();

        $this->assertEquals('current@getkirby.com', $api->user()->email());
        $this->assertEquals('test@getkirby.com', $api->user('test@getkirby.com')->email());

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The user "nope@getkirby.com" cannot be found');
        $this->api->user('nope@getkirby.com');
    }

    public function testUsers()
    {
        $this->assertEquals($this->app->users(), $this->api->users());
    }

    public function testFileGetRoute()
    {

        // regular
        $result = $this->api->call('pages/a/files/a-regular-file.jpg', 'GET');

        $this->assertEquals(200, $result['code']);
        $this->assertEquals('a-regular-file.jpg', $result['data']['filename']);

        // with spaces in filename
        $result = $this->api->call('pages/a/files/a filename with spaces.jpg', 'GET');

        $this->assertEquals(200, $result['code']);
        $this->assertEquals('a filename with spaces.jpg', $result['data']['filename']);
    }

    public function testAuthenticationWithoutCsrf()
    {
        $auth = $this->createMock(Auth::class);
        $auth->method('type')->willReturn('session');
        $auth->method('csrf')->willReturn(false);

        $kirby = $this->createMock(App::class);
        $kirby->method('auth')->willReturn($auth);

        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Unauthenticated');

        $function = require $this->app->root('kirby') . '/config/api/authentication.php';

        $api = new Api([
            'kirby' => $kirby
        ]);

        $function->call($api);
    }

    public function testAuthenticationWithoutUser()
    {
        $auth = $this->createMock(Auth::class);
        $auth->method('user')->willReturn(null);

        $kirby = $this->createMock(App::class);
        $kirby->method('auth')->willReturn($auth);

        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Unauthenticated');

        $function = require $this->app->root('kirby') . '/config/api/authentication.php';

        $api = new Api([
            'kirby' => $kirby
        ]);

        $function->call($api);
    }

    public function testParent()
    {
        $app = $this->app->clone([
            'site' => [
                'files' => [
                    ['filename' => 'sitefile.jpg']
                ]
            ],
            'users' => [
                [
                    'email' => 'current@getkirby.com',
                ],
                [
                    'email' => 'test@getkirby.com',
                    'files' => [
                        ['filename' => 'userfile.jpg']
                    ]
                ]
            ],
        ]);

        $app->impersonate('current@getkirby.com');

        $api = $app->api();

        $this->assertInstanceOf(User::class, $api->parent('account'));
        $this->assertInstanceOf(User::class, $api->parent('users/test@getkirby.com'));
        $this->assertInstanceOf(Site::class, $api->parent('site'));
        $this->assertInstanceOf(Page::class, $api->parent('pages/a+aa'));
        $this->assertInstanceOf(File::class, $api->parent('site/files/sitefile.jpg'));
        $this->assertInstanceOf(File::class, $api->parent('pages/a/files/a-regular-file.jpg'));
        $this->assertInstanceOf(File::class, $api->parent('users/test@getkirby.com/files/userfile.jpg'));

        // model type is not recognized
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid model type: something');
        $this->assertNull($api->parent('something/something'));

        // model cannot be found
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The page cannot be found');
        $this->assertNull($api->parent('pages/does-not-exist'));
    }

    public function testFieldApi()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'title' => 'Test Title',
                            'cover' => [
                                'a.jpg'
                            ]
                        ],
                        'files' => [
                            ['filename' => 'a.jpg'],
                            ['filename' => 'b.jpg'],
                        ],
                        'blueprint' => [
                            'title' => 'Test',
                            'name' => 'test',
                            'fields' => [
                                'cover' => [
                                    'type' => 'files',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page = $app->page('test');
        $response = $app->api()->fieldApi($page, 'cover');

        $this->assertCount(2, $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);
        $this->assertCount(2, $response['data']);
        $this->assertSame('a.jpg', $response['data'][0]['filename']);
        $this->assertSame('b.jpg', $response['data'][1]['filename']);
    }

    public function testFieldApiInvalidField()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The field "nonexists" could not be found');

        $page = $app->page('test');
        $app->api()->fieldApi($page, 'nonexists');
    }

    public function testFieldApiEmptyField()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('No field could be loaded');

        $page = $app->page('test');
        $app->api()->fieldApi($page, '');
    }
}
