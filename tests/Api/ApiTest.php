<?php

namespace Kirby\Api;

use stdClass;
use PHPUnit\Framework\TestCase;

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
                    'pattern' => 'test',
                    'method'  => 'POST',
                    'action'  => function () {
                        return $this->requestQuery('foo');
                    }
                ]
            ]
        ]);

        $result = $api->call('test', 'POST', [
            'query' => ['foo' => 'bar']
        ]);

        $this->assertEquals('bar', $result);
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
        $result = $api->resolve(new MockModel);
        $this->assertInstanceOf(Model::class, $result);

        // resolve class without namespace
        $result = $api->resolve(new stdClass);
        $this->assertInstanceOf(Model::class, $result);

        // resolve class extension
        $result = $api->resolve(new ExtendedModel);
        $this->assertInstanceOf(Model::class, $result);
    }

    public function testModelResolverWithMissingModel()
    {
        $this->expectException('Kirby\Exception\NotFoundException');

        $api = new Api([]);
        $api->resolve(new MockModel);
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

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode(['status' => 'ok']), $result->body());
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
            'status'  => 'error',
            'message' => 'nope',
            'code'    => 500,
            'route'   => 'test'
        ];

        $this->assertInstanceOf('Kirby\Http\Response', $result);
        $this->assertEquals(json_encode($expected), $result->body());
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
}
