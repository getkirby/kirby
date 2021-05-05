<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\Response;
use Kirby\Cms\User;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\TestCase;
use stdClass;

class MockModel
{
}

class ExtendedModel extends stdClass
{
}

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

    public function testConstruct()
    {
        $api = new Api([]);

        $this->assertNull($api->authentication());
        $this->assertEquals([], $api->collections());
        $this->assertEquals([], $api->data());
        $this->assertFalse($api->debug());
        $this->assertEquals([], $api->models());
        $this->assertEquals(['query' => [], 'body' => [], 'files' => []], $api->requestData());
        $this->assertEquals('GET', $api->requestMethod());
        $this->assertEquals([], $api->routes());
    }

    public function test__call()
    {
        $api = new Api([
            'data' => [
                'foo' => 'bar'
            ]
        ]);

        $this->assertEquals('bar', $api->foo());
    }

    public function testAuthentication()
    {
        $phpunit = $this;

        $api = new Api([
            'data' => [
                'foo' => 'bar'
            ],
            'authentication' => $callback = function () use ($phpunit) {
                $phpunit->assertEquals('bar', $this->foo());
            }
        ]);

        $this->assertEquals($callback, $api->authentication());
        $api->authenticate();
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


    public function testCall()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'testScalar',
                    'method'  => 'POST',
                    'action'  => function () {
                        return $this->requestQuery('foo');
                    }
                ],
                [
                    'pattern' => 'testModel',
                    'method'  => 'POST',
                    'action'  => function () {
                        return $this->model('test', 'Awesome test model as string, yay');
                    }
                ],
                [
                    'pattern' => 'testResponse',
                    'method'  => 'POST',
                    'action'  => function () {
                        return new Response('test', 'text/plain', 201);
                    }
                ]
            ],
            'models' => [
                'test' => [
                    'fields' => [
                        'value' => function ($model) {
                            return $model;
                        }
                    ]
                ]
            ]
        ]);

        $result = $api->call('testScalar', 'POST', [
            'query' => ['foo' => 'bar']
        ]);
        $this->assertEquals('bar', $result);

        $result = $api->call('testModel', 'POST');
        $this->assertEquals([
            'code'   => 200,
            'data'   => [
                'value' => 'Awesome test model as string, yay'
            ],
            'status' => 'ok',
            'type'   => 'model'
        ], $result);

        $result = $api->call('testResponse', 'POST');
        $this->assertEquals(new Response('test', 'text/plain', 201), $result);
    }

    public function testCallLocale()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'foo',
                    'method'  => 'GET',
                    'action'  => function () {
                        return 'something';
                    }
                ],
            ],
            'authentication' => function () use (&$language) {
                return new User(['language' => $language]);
            }
        ]);

        $originalLocale = setlocale(LC_CTYPE, 0);

        $language = 'de';
        $this->assertEquals('something', $api->call('foo'));
        $this->assertTrue(in_array(setlocale(LC_MONETARY, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));
        $this->assertTrue(in_array(setlocale(LC_NUMERIC, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));
        $this->assertTrue(in_array(setlocale(LC_TIME, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));
        $this->assertEquals($originalLocale, setlocale(LC_CTYPE, 0));

        $language = 'pt_BR';
        $this->assertEquals('something', $api->call('foo'));
        $this->assertTrue(in_array(setlocale(LC_MONETARY, 0), ['pt', 'pt_BR', 'pt_BR.UTF-8', 'pt_BR.UTF8', 'pt_BR.ISO8859-1']));
        $this->assertTrue(in_array(setlocale(LC_NUMERIC, 0), ['pt', 'pt_BR', 'pt_BR.UTF-8', 'pt_BR.UTF8', 'pt_BR.ISO8859-1']));
        $this->assertTrue(in_array(setlocale(LC_TIME, 0), ['pt', 'pt_BR', 'pt_BR.UTF-8', 'pt_BR.UTF8', 'pt_BR.ISO8859-1']));
        $this->assertEquals($originalLocale, setlocale(LC_CTYPE, 0));
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

    public function testCollections()
    {
        $api = new Api([
            'models' => [
                'test' => [
                    'fields' => [
                        'id' => function ($object) {
                            return $object->id();
                        }
                    ],
                    'type' => 'Kirby\Toolkit\Obj'
                ]
            ],
            'collections' => [
                'test' => [
                    'model' => 'test',
                    'type'  => 'Kirby\Toolkit\Collection',
                ]
            ]
        ]);

        $instance = new \Kirby\Toolkit\Collection([
            new \Kirby\Toolkit\Obj(['id' => 'a']),
            new \Kirby\Toolkit\Obj(['id' => 'b']),
        ]);

        $collection = $api->collection('test', $instance);
        $data       = $collection->toArray();
        $expected   = [
            ['id' => 'a'],
            ['id' => 'b'],
        ];

        $this->assertEquals($expected, $data);

        // missing collection
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The collection "not-available" does not exist');

        $api->collection('not-available', $instance);
    }

    public function testData()
    {
        $api = new Api([
            'data' => $data = [
                'a' => 'A',
                'b' => function () {
                    return 'B';
                },
                'c' => function ($value) {
                    return $value;
                }
            ]
        ]);

        $this->assertEquals($data, $api->data());
        $this->assertEquals('A', $api->data('a'));
        $this->assertEquals('B', $api->data('b'));
        $this->assertEquals('C', $api->data('c', 'C'));

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Api data for "d" does not exist');

        $api->data('d');
    }

    public function testDebug()
    {
        $api = new Api([
            'debug' => true
        ]);

        $this->assertTrue($api->debug());
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

    public function testModels()
    {
        $api = new Api([
            'models' => [
                'test' => [
                    'fields' => [
                        'id' => function ($object) {
                            return $object->id();
                        }
                    ],
                    'type' => 'Kirby\Toolkit\Obj'
                ]
            ]
        ]);

        $instance = new \Kirby\Toolkit\Obj(['id' => 'a']);
        $model    = $api->model('test', $instance);
        $data     = $model->toArray();
        $expected = ['id' => 'a'];

        $this->assertEquals($expected, $data);

        // missing model
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The model "not-available" does not exist');

        $api->model('not-available', $instance);
    }

    public function testModelResolver()
    {
        $api = new Api([
            'models' => [
                'MockModel' => [
                    'type' => MockModel::class,
                ],
                'stdClass' => [
                    'type' => stdClass::class,
                ]
            ]
        ]);

        // resolve class with namespace
        $result = $api->resolve(new MockModel());
        $this->assertInstanceOf(Model::class, $result);

        // resolve class without namespace
        $result = $api->resolve(new stdClass());
        $this->assertInstanceOf(Model::class, $result);

        // resolve class extension
        $result = $api->resolve(new ExtendedModel());
        $this->assertInstanceOf(Model::class, $result);
    }

    public function testModelResolverWithMissingModel()
    {
        $this->expectException('Kirby\Exception\NotFoundException');

        $api = new Api([]);
        $api->resolve(new MockModel());
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

        $this->assertInstanceOf('Kirby\Cms\User', $api->parent('account'));
        $this->assertInstanceOf('Kirby\Cms\User', $api->parent('users/test@getkirby.com'));
        $this->assertInstanceOf('Kirby\Cms\Site', $api->parent('site'));
        $this->assertInstanceOf('Kirby\Cms\Page', $api->parent('pages/a+aa'));
        $this->assertInstanceOf('Kirby\Cms\File', $api->parent('site/files/sitefile.jpg'));
        $this->assertInstanceOf('Kirby\Cms\File', $api->parent('pages/a/files/a-regular-file.jpg'));
        $this->assertInstanceOf('Kirby\Cms\File', $api->parent('users/test@getkirby.com/files/userfile.jpg'));

        // model type is not recognized
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid model type: something');
        $this->assertNull($api->parent('something/something'));

        // model cannot be found
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The page cannot be found');
        $this->assertNull($api->parent('pages/does-not-exist'));
    }

    public function testRequestData()
    {
        $api = new Api([
            'requestData' => $requestData = [
                'query'   => $query   = ['a' => 'A'],
                'body'    => $body    = ['b' => 'B'],
                'files'   => $files   = ['c' => 'C'],
                'headers' => $headers = ['d' => 'D'],
            ]
        ]);

        $this->assertEquals($requestData, $api->requestData());

        $this->assertEquals($query, $api->requestData('query'));
        $this->assertEquals($query, $api->requestQuery());
        $this->assertEquals('A', $api->requestData('query', 'a'));
        $this->assertEquals('A', $api->requestQuery('a'));
        $this->assertEquals('fallback', $api->requestData('query', 'x', 'fallback'));
        $this->assertEquals('fallback', $api->requestQuery('x', 'fallback'));

        $this->assertEquals($body, $api->requestData('body'));
        $this->assertEquals($body, $api->requestBody());
        $this->assertEquals('B', $api->requestData('body', 'b'));
        $this->assertEquals('B', $api->requestBody('b'));
        $this->assertEquals('fallback', $api->requestData('body', 'x', 'fallback'));
        $this->assertEquals('fallback', $api->requestBody('x', 'fallback'));

        $this->assertEquals($files, $api->requestData('files'));
        $this->assertEquals($files, $api->requestFiles());
        $this->assertEquals('C', $api->requestData('files', 'c'));
        $this->assertEquals('C', $api->requestFiles('c'));
        $this->assertEquals('fallback', $api->requestData('files', 'x', 'fallback'));
        $this->assertEquals('fallback', $api->requestFiles('x', 'fallback'));

        $this->assertEquals($headers, $api->requestData('headers'));
        $this->assertEquals($headers, $api->requestHeaders());
        $this->assertEquals('D', $api->requestData('headers', 'd'));
        $this->assertEquals('D', $api->requestHeaders('d'));
        $this->assertEquals('fallback', $api->requestData('headers', 'x', 'fallback'));
        $this->assertEquals('fallback', $api->requestHeaders('x', 'fallback'));
    }

    public function testRenderString()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        return 'test';
                    }
                ]
            ]
        ]);

        $this->assertEquals('test', $api->render('test', 'POST'));
    }

    public function testRenderArray()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        return ['a' => 'A'];
                    }
                ]
            ]
        ]);

        $result = $api->render('test', 'POST');

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode(['a' => 'A']), $result->body());
    }

    public function testRenderTrue()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        return true;
                    }
                ]
            ]
        ]);

        $result = $api->render('test', 'POST');

        $expected = [
            'status' => 'ok',
            'message' => 'ok',
            'code' => 200
        ];

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode($expected), $result->body());
    }

    public function testRenderFalse()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        return false;
                    }
                ]
            ]
        ]);

        $result = $api->render('test', 'POST');

        $expected = [
            'status'  => 'error',
            'message' => 'bad request',
            'code'    => 400
        ];

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode($expected), $result->body());
    }

    public function testRenderNull()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        return null;
                    }
                ]
            ]
        ]);

        $result = $api->render('test', 'POST');

        $expected = [
            'status'  => 'error',
            'message' => 'not found',
            'code'    => 404
        ];

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode($expected), $result->body());
    }

    public function testRenderException()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        throw new \Exception('nope');
                    }
                ]
            ]
        ]);

        $result = $api->render('test', 'POST');

        $expected = [
            'status'   => 'error',
            'message'  => 'nope',
            'code'     => 500,
            'key'      => null,
            'details'  => []
        ];

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode($expected), $result->body());
    }

    public function testRenderExceptionWithDebugging()
    {
        $api = new Api([
            'debug' => true,
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        throw new \Exception('nope');
                    }
                ]
            ]
        ]);

        // simulate the document root to test relative file paths
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;

        $result = $api->render('test', 'POST');

        $expected = [
            'status'    => 'error',
            'message'   => 'nope',
            'code'      => 500,
            'exception' => 'Exception',
            'key'       => null,
            'file'      => '/' . basename(__FILE__),
            'line'      => __LINE__ - 18,
            'details'   => [],
            'route'     => 'test'
        ];

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode($expected), $result->body());

        unset($_SERVER['DOCUMENT_ROOT']);
    }

    public function testRenderKirbyException()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        throw new \Kirby\Exception\NotFoundException([
                            'key'      => 'test',
                            'fallback' => 'Test',
                            'details'  => [
                                'a' => 'A'
                            ]
                        ]);
                    }
                ]
            ]
        ]);

        $result = $api->render('test', 'POST');

        $expected = [
            'status'  => 'error',
            'message' => 'Test',
            'code'    => 404,
            'key'     => 'error.test',
            'details' => ['a' => 'A'],
        ];

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode($expected), $result->body());
    }

    public function testRenderKirbyExceptionWithDebugging()
    {
        $api = new Api([
            'debug' => true,
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        throw new \Kirby\Exception\NotFoundException([
                            'key'      => 'test',
                            'fallback' => 'Test',
                            'details'  => [
                                'a' => 'A'
                            ]
                        ]);
                    }
                ]
            ]
        ]);

        // simulate the document root to test relative file paths
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;

        $result = $api->render('test', 'POST');

        $expected = [
            'status'    => 'error',
            'message'   => 'Test',
            'code'      => 404,
            'exception' => 'Kirby\\Exception\\NotFoundException',
            'key'       => 'error.test',
            'file'      => '/' . basename(__FILE__),
            'line'      => __LINE__ - 24,
            'details'   => ['a' => 'A'],
            'route'     => 'test',
        ];

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode($expected), $result->body());

        unset($_SERVER['DOCUMENT_ROOT']);
    }

    public function testRenderWithSanitizedErrorCode()
    {
        $api = new Api([
            'routes' => [
                [
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        throw new \Exception('nope', 1000);
                    }
                ]
            ]
        ]);

        $result = $api->render('test', 'POST');

        $this->assertEquals(500, $result->code());
    }

    public function testRequestMethod()
    {
        $api = new Api([
            'requestMethod' => 'POST',
        ]);

        $this->assertEquals('POST', $api->requestMethod());
    }

    public function testRoutes()
    {
        $api = new Api([
            'routes' => $routes = [
                [
                    'pattern' => 'test',
                    'action'  => function () {
                        return 'foo';
                    }
                ]
            ]
        ]);

        $this->assertEquals($routes, $api->routes());
    }

    public function testUpload()
    {
        $api = new Api([
            'requestMethod' => 'POST',
            'requestData' => [
                'files' => [
                    [
                        'name'     => 'test.txt',
                        'tmp_name' => __DIR__ . '/fixtures/tmp/abc',
                        'size'     => 123,
                        'error'    => 0
                    ]
                ]
            ],
            'authentication' => function () {
                return new User(['language' => 'en']);
            }
        ]);

        $phpunit = $this;
        $api->authenticate();

        // move_uploaded_file error
        $data = $api->upload(function ($source) {
            // empty closure
        });

        $phpunit->assertSame([
            'status' => 'error',
            'message' => 'The uploaded file could not be moved'
        ], $data);

        // single
        $uploads = [];
        $data = $api->upload(function ($source, $filename) use ($phpunit, &$uploads) {
            // can't test souce path with dynamic uniqid
            // $phpunit->assertSame('uniqid.test.txt', $source);
            $phpunit->assertSame('test.txt', $filename);

            return $uploads = [
                'filename' => $filename
            ];
        }, true, true);

        $phpunit->assertSame([
            'status' => 'ok',
            'data' => $uploads
        ], $data);

        // multiple
        $uploads = [];
        $data = $api->upload(function ($source, $filename) use ($phpunit, &$uploads) {
            // can't test souce path with dynamic uniqid
            // $phpunit->assertSame('uniqid.test.txt', $source);
            $phpunit->assertSame('test.txt', $filename);

            return $uploads = [
                'filename' => $filename
            ];
        }, false, true);

        $phpunit->assertSame([
            'status' => 'ok',
            'data' => $uploads
        ], $data);
    }

    public function testUploadMultiple()
    {
        $api = new Api([
            'requestMethod' => 'POST',
            'requestData' => [
                'files' => [
                    [
                        'name'     => 'foo.txt',
                        'tmp_name' => __DIR__ . '/fixtures/tmp/foo',
                        'size'     => 123,
                        'error'    => 0
                    ],
                    [
                        'name'     => 'bar.txt',
                        'tmp_name' => __DIR__ . '/fixtures/tmp/bar',
                        'size'     => 123,
                        'error'    => 0
                    ]
                ]
            ],
            'authentication' => function () {
                return new User(['language' => 'en']);
            }
        ]);

        $phpunit = $this;
        $api->authenticate();

        $uploads = [];
        $data = $api->upload(function ($source, $filename) use ($phpunit, &$uploads) {
            return [
                'filename' => $filename
            ];
        }, false, true);

        $phpunit->assertSame([
            'status' => 'ok',
            'data' => [
                'foo.txt' => ['filename' => 'foo.txt'],
                'bar.txt' => ['filename' => 'bar.txt'],
            ]
        ], $data);
    }

    public function testUploadFail()
    {
        $api = new Api([
            'requestMethod' => 'POST',
            'requestData' => [
                'files' => [ ]
            ]
        ]);

        $this->expectException('Exception');
        $api->upload(function ($source) {
            // empty closure
        });
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
}
