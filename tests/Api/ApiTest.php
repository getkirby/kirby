<?php

namespace Kirby\Api;

use Exception;
use Kirby\Cms\Response;
use Kirby\Cms\User;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response as HttpResponse;
use Kirby\TestCase;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;
use stdClass;

class MockModel
{
}

class ExtendedModel extends stdClass
{
}

class ApiTest extends TestCase
{
	public function testConstruct(): void
	{
		$api = new Api([]);

		$this->assertNull($api->authentication());
		$this->assertSame([], $api->collections());
		$this->assertSame([], $api->data());
		$this->assertFalse($api->debug());
		$this->assertSame([], $api->models());
		$this->assertSame(['query' => [], 'body' => [], 'files' => []], $api->requestData());
		$this->assertSame('GET', $api->requestMethod());
		$this->assertSame([], $api->routes());
	}

	public function test__call(): void
	{
		$api = new Api([
			'data' => [
				'foo' => 'bar'
			]
		]);

		$this->assertSame('bar', $api->foo());
	}

	public function testAuthentication(): void
	{
		$phpunit = $this;

		$api = new Api([
			'data' => [
				'foo' => 'bar'
			],
			'authentication' => $callback = function () use ($phpunit) {
				$phpunit->assertSame('bar', $this->foo());
			}
		]);

		$this->assertSame($callback, $api->authentication());
		$api->authenticate();
	}

	public function testCall(): void
	{
		$api = new Api([
			'routes' => [
				[
					'pattern' => 'testScalar',
					'method'  => 'POST',
					'action'  => fn () => $this->requestQuery('foo')
				],
				[
					'pattern' => 'testModel',
					'method'  => 'POST',
					'action'  => fn () => $this->model('test', 'Awesome test model as string, yay')
				],
				[
					'pattern' => 'testResponse',
					'method'  => 'POST',
					'action'  => fn () => new Response('test', 'text/plain', 201)
				]
			],
			'models' => [
				'test' => [
					'fields' => [
						'value' => fn ($model) => $model
					]
				]
			]
		]);

		$result = $api->call('testScalar', 'POST', [
			'query' => ['foo' => 'bar']
		]);
		$this->assertSame('bar', $result);

		$result = $api->call('testModel', 'POST');
		$this->assertSame([
			'code'   => 200,
			'data'   => [
				'value' => 'Awesome test model as string, yay'
			],
			'status' => 'ok',
			'type'   => 'model'
		], $result);

		$result = $api->call('testResponse', 'POST');
		$this->assertEquals(new Response('test', 'text/plain', 201), $result); // cannot use strict assertion (test for object contents)
	}

	public function testCallLocale(): void
	{
		$originalLocale = setlocale(LC_CTYPE, 0);

		$language = 'de';

		$api = new Api([
			'routes' => [
				[
					'pattern' => 'foo',
					'method'  => 'GET',
					'action'  => fn () => 'something'
				],
			],
			'authentication' => function () use (&$language) {
				return new User(['language' => $language]);
			}
		]);

		$this->assertSame('something', $api->call('foo'));
		$this->assertTrue(in_array(setlocale(LC_MONETARY, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));
		$this->assertTrue(in_array(setlocale(LC_NUMERIC, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));
		$this->assertTrue(in_array(setlocale(LC_TIME, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));
		$this->assertSame($originalLocale, setlocale(LC_CTYPE, 0));

		$language = 'pt_BR';
		$this->assertSame('something', $api->call('foo'));
		$this->assertTrue(in_array(setlocale(LC_MONETARY, 0), ['pt', 'pt_BR', 'pt_BR.UTF-8', 'pt_BR.UTF8', 'pt_BR.ISO8859-1']));
		$this->assertTrue(in_array(setlocale(LC_NUMERIC, 0), ['pt', 'pt_BR', 'pt_BR.UTF-8', 'pt_BR.UTF8', 'pt_BR.ISO8859-1']));
		$this->assertTrue(in_array(setlocale(LC_TIME, 0), ['pt', 'pt_BR', 'pt_BR.UTF-8', 'pt_BR.UTF8', 'pt_BR.ISO8859-1']));
		$this->assertSame($originalLocale, setlocale(LC_CTYPE, 0));
	}

	public function testCollections(): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'fields' => [
						'id' => fn ($object) => $object->id()
					],
					'type' => Obj::class
				]
			],
			'collections' => [
				'test' => [
					'model' => 'test',
					'type'  => Collection::class,
				]
			]
		]);

		$instance = new Collection([
			new Obj(['id' => 'a']),
			new Obj(['id' => 'b']),
		]);

		$collection = $api->collection('test', $instance);
		$data       = $collection->toArray();
		$expected   = [
			['id' => 'a'],
			['id' => 'b'],
		];

		$this->assertSame($expected, $data);

		// missing collection
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The collection "not-available" does not exist');

		$api->collection('not-available', $instance);
	}

	public function testData(): void
	{
		$api = new Api([
			'data' => $data = [
				'a' => 'A',
				'b' => fn () => 'B',
				'c' => fn ($value) => $value
			]
		]);

		$this->assertSame($data, $api->data());
		$this->assertSame('A', $api->data('a'));
		$this->assertSame('B', $api->data('b'));
		$this->assertSame('C', $api->data('c', 'C'));

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Api data for "d" does not exist');

		$api->data('d');
	}

	public function testDebug(): void
	{
		$api = new Api([
			'debug' => true
		]);

		$this->assertTrue($api->debug());
	}

	public function testModels(): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'fields' => [
						'id' => fn ($object) => $object->id()
					],
					'type' => Obj::class
				]
			]
		]);

		$instance = new Obj(['id' => 'a']);
		$model    = $api->model('test', $instance);
		$data     = $model->toArray();
		$expected = ['id' => 'a'];

		$this->assertSame($expected, $data);

		// missing model
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The model "not-available" does not exist');

		$api->model('not-available', $instance);
	}

	public function testModelResolver(): void
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

	public function testModelResolverWithMissingModel(): void
	{
		$this->expectException(NotFoundException::class);

		$api = new Api([]);
		$api->resolve(new MockModel());
	}

	public function testRequestData(): void
	{
		$api = new Api([
			'requestData' => $requestData = [
				'query'   => $query   = ['a' => 'A'],
				'body'    => $body    = ['b' => 'B'],
				'files'   => $files   = ['c' => 'C'],
				'headers' => $headers = ['d' => 'D'],
			]
		]);

		$this->assertSame($requestData, $api->requestData());

		$this->assertSame($query, $api->requestData('query'));
		$this->assertSame($query, $api->requestQuery());
		$this->assertSame('A', $api->requestData('query', 'a'));
		$this->assertSame('A', $api->requestQuery('a'));
		$this->assertSame('fallback', $api->requestData('query', 'x', 'fallback'));
		$this->assertSame('fallback', $api->requestQuery('x', 'fallback'));

		$this->assertSame($body, $api->requestData('body'));
		$this->assertSame($body, $api->requestBody());
		$this->assertSame('B', $api->requestData('body', 'b'));
		$this->assertSame('B', $api->requestBody('b'));
		$this->assertSame('fallback', $api->requestData('body', 'x', 'fallback'));
		$this->assertSame('fallback', $api->requestBody('x', 'fallback'));

		$this->assertSame($files, $api->requestData('files'));
		$this->assertSame($files, $api->requestFiles());
		$this->assertSame('C', $api->requestData('files', 'c'));
		$this->assertSame('C', $api->requestFiles('c'));
		$this->assertSame('fallback', $api->requestData('files', 'x', 'fallback'));
		$this->assertSame('fallback', $api->requestFiles('x', 'fallback'));

		$this->assertSame($headers, $api->requestData('headers'));
		$this->assertSame($headers, $api->requestHeaders());
		$this->assertSame('D', $api->requestData('headers', 'd'));
		$this->assertSame('D', $api->requestHeaders('d'));
		$this->assertSame('fallback', $api->requestData('headers', 'x', 'fallback'));
		$this->assertSame('fallback', $api->requestHeaders('x', 'fallback'));
	}

	public function testRenderString(): void
	{
		$api = new Api([
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => fn () => 'test'
				]
			]
		]);

		$this->assertSame('test', $api->render('test', 'POST'));
	}

	public function testRenderArray(): void
	{
		$api = new Api([
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => fn () => ['a' => 'A']
				]
			]
		]);

		$result = $api->render('test', 'POST');

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode(['a' => 'A']), $result->body());
	}

	public function testRenderTrue(): void
	{
		$api = new Api([
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => fn () => true
				]
			]
		]);

		$result = $api->render('test', 'POST');

		$expected = [
			'status'  => 'ok',
			'message' => 'ok',
			'code'    => 200
		];

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode($expected), $result->body());
	}

	public function testRenderFalse(): void
	{
		$api = new Api([
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => fn () => false
				]
			]
		]);

		$result = $api->render('test', 'POST');

		$expected = [
			'status'  => 'error',
			'message' => 'bad request',
			'code'    => 400
		];

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode($expected), $result->body());
	}

	public function testRenderNull(): void
	{
		$api = new Api([
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => fn () => null
				]
			]
		]);

		$result = $api->render('test', 'POST');

		$expected = [
			'status'  => 'error',
			'message' => 'not found',
			'code'    => 404
		];

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode($expected), $result->body());
	}

	public function testRenderException(): void
	{
		$api = new Api([
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => function () {
						throw new Exception('nope');
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

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode($expected), $result->body());
	}

	public function testRenderExceptionWithDebugging(): void
	{
		$api = new Api([
			'debug' => true,
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => function () {
						throw new Exception('nope');
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

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode($expected), $result->body());

		unset($_SERVER['DOCUMENT_ROOT']);
	}

	public function testRenderKirbyException(): void
	{
		$api = new Api([
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => function () {
						throw new NotFoundException(
							key: 'test',
							fallback: 'Test',
							details: ['a' => 'A']
						);
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

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode($expected), $result->body());
	}

	public function testRenderKirbyExceptionWithDebugging(): void
	{
		$api = new Api([
			'debug' => true,
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => function () {
						throw new NotFoundException(
							key: 'test',
							fallback: 'Test',
							details: ['a' => 'A']
						);
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
			'exception' => NotFoundException::class,
			'key'       => 'error.test',
			'file'      => '/' . basename(__FILE__),
			'line'      => __LINE__ - 22,
			'details'   => ['a' => 'A'],
			'route'     => 'test',
		];

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode($expected), $result->body());

		unset($_SERVER['DOCUMENT_ROOT']);
	}

	public function testRenderWithSanitizedErrorCode(): void
	{
		$api = new Api([
			'routes' => [
				[
					'pattern' => 'test',
					'method'  => 'POST',
					'action'  => function () {
						throw new Exception('nope', 1000);
					}
				]
			]
		]);

		$result = $api->render('test', 'POST');

		$this->assertSame(500, $result->code());
	}

	public function testRequestMethod(): void
	{
		$api = new Api([
			'requestMethod' => 'POST',
		]);

		$this->assertSame('POST', $api->requestMethod());
	}

	public function testRoutes(): void
	{
		$api = new Api([
			'routes' => $routes = [
				[
					'pattern' => 'test',
					'action'  => fn () => 'foo'
				]
			]
		]);

		$this->assertSame($routes, $api->routes());
	}

	public function testUpload(): void
	{
		$api = new Api([
			'requestMethod' => 'POST',
			'requestData'   => [
				'files' => [
					[
						'name'     => 'test.txt',
						'tmp_name' => KIRBY_TMP_DIR . '/api.api/abc',
						'size'     => 123,
						'error'    => 0
					]
				]
			],
			'authentication' => fn () => new User(['language' => 'en'])
		]);

		$phpunit = $this;
		$api->authenticate();

		// move_uploaded_file error
		$data = $api->upload(function ($source) {
			// empty closure
		});

		$phpunit->assertSame([
			'status'  => 'error',
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
			'data'   => $uploads
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
			'data'   => $uploads
		], $data);
	}
}
