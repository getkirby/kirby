<?php

namespace Kirby\Api;

use Exception;
use Kirby\Auth\Auth;
use Kirby\Cms\App;
use Kirby\Cms\Response;
use Kirby\Cms\User;
use Kirby\Exception\AuthException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response as HttpResponse;
use Kirby\TestCase;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\I18n;
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
	public const TMP = KIRBY_TMP_DIR . '/Cms.Api';

	protected Api $api;
	protected string $locale;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => [
							'title' => 'A little fanzy page'
						],
						'children' => [
							['slug' => 'aa'],
							['slug' => 'ab'],
						],
						'files' => [
							['filename' => 'a-regular-file.jpg'],
							['filename' => 'a filename with spaces.jpg']
						]
					],
					['slug' => 'b']
				]
			],
			'options' => [
				'api' => [
					'allowImpersonation' => true,
					'authentication'     => fn () => true,
					'routes'             => [
						[
							'pattern' => 'foo',
							'method'  => 'GET',
							'action'  => fn () => 'something'
						]
					]
				],
				'locale' => 'de_DE.UTF-8'
			],
		]);

		$this->app->impersonate('kirby');
		$this->api = $this->app->api();

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		setlocale(LC_ALL, 'C');
		Dir::remove(static::TMP);
	}

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

	public function testAuthenticationWithoutCsrf(): void
	{
		$auth = $this->createStub(Auth::class);
		$auth->method('type')->willReturn('session');
		$auth->method('csrf')->willReturn(false);

		$kirby = $this->createStub(App::class);
		$kirby->method('auth')->willReturn($auth);

		$this->expectException(AuthException::class);
		$this->expectExceptionMessage('Unauthenticated');

		$function = require $this->app->root('kirby') . '/config/api/authentication.php';

		$api = new Api([
			'kirby' => $kirby
		]);

		$function->call($api);
	}

	public function testAuthenticationWithoutUser(): void
	{
		$auth = $this->createStub(Auth::class);
		$auth->method('user')->willReturn(null);

		$kirby = $this->createStub(App::class);
		$kirby->method('auth')->willReturn($auth);

		$this->expectException(AuthException::class);
		$this->expectExceptionMessage('Unauthenticated');

		$function = require $this->app->root('kirby') . '/config/api/authentication.php';

		$api = new Api([
			'kirby' => $kirby
		]);

		$function->call($api);
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
		$this->assertTrue(in_array(setlocale(LC_CTYPE, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));

		$this->assertTrue(in_array(setlocale(LC_MONETARY, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));
		$this->assertTrue(in_array(setlocale(LC_NUMERIC, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));
		$this->assertTrue(in_array(setlocale(LC_TIME, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));

		$language = 'pt_BR';
		$this->assertSame('something', $api->call('foo'));
		$this->assertTrue(in_array(setlocale(LC_MONETARY, 0), ['pt', 'pt_BR', 'pt_BR.UTF-8', 'pt_BR.UTF8', 'pt_BR.ISO8859-1']));
		$this->assertTrue(in_array(setlocale(LC_NUMERIC, 0), ['pt', 'pt_BR', 'pt_BR.UTF-8', 'pt_BR.UTF8', 'pt_BR.ISO8859-1']));
		$this->assertTrue(in_array(setlocale(LC_TIME, 0), ['pt', 'pt_BR', 'pt_BR.UTF-8', 'pt_BR.UTF8', 'pt_BR.ISO8859-1']));

		// should still be the result from App::setLanguage()
		// while all above are changed according to the user language
		// via Api::setLocale()
		$this->assertTrue(in_array(setlocale(LC_CTYPE, 0), ['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']));
	}

	public function testCallLocaleSingleLang1(): void
	{
		$this->assertSame('C', setlocale(LC_ALL, 0));
		$this->assertSame('something', $this->api->call('foo'));
		$this->assertSame('de_DE.UTF-8', setlocale(LC_ALL, 0));
	}

	public function testCallLocaleSingleLang2(): void
	{
		setlocale(LC_ALL, 'C');
		$this->assertSame('C', setlocale(LC_ALL, 0));

		$_GET['language'] = 'en';

		$this->assertSame('something', $this->api->call('foo'));
		$this->assertSame('de_DE.UTF-8', setlocale(LC_ALL, 0));

		$_GET = [];
	}

	public function testCallLocaleMultiLang1(): void
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

	public function testCallLocaleMultiLang2(): void
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

		$this->assertSame('something', $this->api->call('foo', 'GET', [
			'query' => ['language' => 'de']
		]));
		$this->assertSame('de_AT.UTF-8', setlocale(LC_ALL, 0));

		$_GET = [];
	}

	public function testCallTranslation(): void
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

	public function testFieldApi(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'title' => 'Test Title',
							'cover' => ['a.jpg']
						],
						'files' => [
							['filename' => 'a.jpg'],
							['filename' => 'b.jpg'],
						],
						'blueprint' => [
							'title'  => 'Test',
							'name'   => 'test',
							'fields' => [
								'cover' => [
									'type' => 'files',
								]
							]
						]
					]
				]
			],
			'request' => [
				'query' => [
					'items' => 'a.jpg,b.jpg'
				],
			]
		]);
		$app->impersonate('kirby');

		$page     = $app->page('test');
		$response = $app->api()->fieldApi($page, 'cover', 'items');

		$this->assertCount(2, $response);
		$this->assertSame('a.jpg', $response[0]['filename']);
		$this->assertSame('b.jpg', $response[1]['filename']);
	}

	public function testFieldApiInvalidField(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The field could not be found');

		$page = $app->page('test');
		$app->api()->fieldApi($page, 'nonexists');
	}

	public function testFieldApiEmptyField(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The field could not be found');

		$page = $app->page('test');
		$app->api()->fieldApi($page, '');
	}

	public function testFile(): void
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

		$this->assertSame('test.jpg', $api->file('site', 'test.jpg')->filename());
		$this->assertSame('test.jpg', $api->file('pages/a', 'test.jpg')->filename());
		$this->assertSame('test.jpg', $api->file('pages/a+a', 'test.jpg')->filename());
		$this->assertSame('test.jpg', $api->file('users/test@getkirby.com', 'test.jpg')->filename());
	}

	public function testFileGetRoute(): void
	{
		// regular
		$result = $this->api->call('pages/a/files/a-regular-file.jpg', 'GET');

		$this->assertSame(200, $result['code']);
		$this->assertSame('a-regular-file.jpg', $result['data']['filename']);

		// with spaces in filename
		$result = $this->api->call('pages/a/files/a filename with spaces.jpg', 'GET');

		$this->assertSame(200, $result['code']);
		$this->assertSame('a filename with spaces.jpg', $result['data']['filename']);
	}

	public function testFileNotFound(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The file "nope.jpg" cannot be found');

		$this->api->file('site', 'nope.jpg');
	}

	public function testFileNotReadable(): void
	{
		$this->expectException(NotFoundException::class);
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

	public function testLanguage(): void
	{
		$api = $this->api->clone([
			'requestData' => [
				'headers' => [
					'x-language' => 'de'
				]
			]
		]);

		$this->assertSame('de', $api->language());
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

	public function testPage(): void
	{
		$a  = $this->app->page('a');
		$aa = $this->app->page('a/aa');

		$this->assertSame($a, $this->api->page('a'));
		$this->assertSame($aa, $this->api->page('a+aa'));

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "does-not-exist" cannot be found');
		$this->api->page('does-not-exist');
	}

	public function testPages(): void
	{
		$this->assertSame(['a/aa', 'a/ab'], $this->api->pages('a')->keys());
	}

	public function testPagesNotAccessible(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'pages/api-protected' => [
					'options' => ['access' => false]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' 	   => 'a'
					],
					[
						'slug' 	   => 'b',
						'template' => 'api-protected'
					],
					[
						'slug' 	   => 'c'
					]
				]
			],
			'users' => [
				['id' => 'bastian', 'role' => 'admin']
			]
		]);

		$app->impersonate('bastian');

		$this->assertSame(['a', 'c'], $app->api()->pages()->keys());
	}


	public function testParent(): void
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
					'role'  => 'admin'
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

		$this->assertIsUser($api->parent('account'));
		$this->assertIsUser($api->parent('users/test@getkirby.com'));
		$this->assertIsSite($api->parent('site'));
		$this->assertIsPage($api->parent('pages/a+aa'));
		$this->assertIsFile($api->parent('site/files/sitefile.jpg'));
		$this->assertIsFile($api->parent('pages/a/files/a-regular-file.jpg'));
		$this->assertIsFile($api->parent('users/test@getkirby.com/files/userfile.jpg'));

		// model type is not recognized
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid model type: something');
		$this->assertNull($api->parent('something/something'));

		// model cannot be found
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page cannot be found');
		$this->assertNull($api->parent('pages/does-not-exist'));
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
			'file'      => basename(__FILE__),
			'line'      => __LINE__ - 18,
			'details'   => [],
			'route'     => 'test'
		];

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode($expected), $result->body());

		unset($_SERVER['DOCUMENT_ROOT']);
	}

	public function testRenderExceptionWithDebugging2(): void
	{
		// simulate the document root to test relative file paths
		$app = $this->app->clone([
			'server' => [
				'DOCUMENT_ROOT' => __DIR__
			]
		]);

		$api = new Api([
			'debug' => true,
			'kirby' => $app,
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
			'status'    => 'error',
			'message'   => 'nope',
			'code'      => 500,
			'exception' => 'Exception',
			'key'       => null,
			'file'      => '/' . basename(__FILE__),
			'line'      => __LINE__ - 15,
			'details'   => [],
			'route'     => 'test'
		];

		$this->assertInstanceOf(HttpResponse::class, $result);
		$this->assertSame(json_encode($expected), $result->body());
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
			'file'      => basename(__FILE__),
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

	public function testSearchPages(): void
	{
		$api = new Api([
			'requestMethod' => 'GET',
			'requestData'   => [
				'query' => [
					'q'      => 'fanzy',
					'status' => 'published'
				]
			]
		]);

		$this->assertSame('a', $api->searchPages()->first()->id());

		$api = new Api([
			'requestMethod' => 'POST',
			'requestData'   => [
				'query' => [
					'status' => 'published'
				],
				'body' => [
					'search' => 'fanzy'
				]
			]
		]);

		$this->assertSame('a', $api->searchPages()->first()->id());
	}

	public function testSectionApi(): void
	{
		$app = $this->app->clone([
			'sections' => [
				'test' => [
					'api' => fn () => [
						[
							'pattern' => '/message',
							'action'  => fn () => [
								'message' => 'Test'
							]
						]
					]
				]
			],
			'blueprints' => [
				'pages/test' => [
					'title' => 'Test',
					'name' => 'test',
					'sections' => [
						'test' => [
							'type' => 'test',
						]
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
					]
				]
			]
		]);

		$app->impersonate('kirby');
		$page = $app->page('test');

		$response = $app->api()->sectionApi($page, 'test', 'message');
		$this->assertSame('Test', $response['message']);
	}

	public function testSectionApiWithInvalidSection(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The section "nonexists" could not be found');

		$page = $app->page('test');
		$app->api()->sectionApi($page, 'nonexists');
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

	public function testUser(): void
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

		$this->assertSame('current@getkirby.com', $api->user()->email());
		$this->assertSame('test@getkirby.com', $api->user('test@getkirby.com')->email());

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "nope@getkirby.com" cannot be found');
		$this->api->user('nope@getkirby.com');
	}

	public function testUsers(): void
	{
		$this->assertSame($this->app->users(), $this->api->users());
	}
}
