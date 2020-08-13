<?php

namespace Kirby\Api;

use Kirby\Cms\Response;
use Kirby\Cms\User;
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
        $originalLocale = setlocale(LC_CTYPE, 0);

        $language = 'de';

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
}
