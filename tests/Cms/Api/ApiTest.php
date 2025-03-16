<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\AuthException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use Kirby\Toolkit\I18n;

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

		$this->locale = setlocale(LC_ALL, 0);
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
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

		$this->assertSame('something', $this->api->call('foo', 'GET', [
			'query' => ['language' => 'de']
		]));
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

		$this->assertSame('de', $api->language());
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

		$this->assertSame('test.jpg', $api->file('site', 'test.jpg')->filename());
		$this->assertSame('test.jpg', $api->file('pages/a', 'test.jpg')->filename());
		$this->assertSame('test.jpg', $api->file('pages/a+a', 'test.jpg')->filename());
		$this->assertSame('test.jpg', $api->file('users/test@getkirby.com', 'test.jpg')->filename());
	}

	public function testFileNotFound()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The file "nope.jpg" cannot be found');

		$this->api->file('site', 'nope.jpg');
	}

	public function testFileNotReadable()
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

	public function testPage()
	{
		$a  = $this->app->page('a');
		$aa = $this->app->page('a/aa');

		$this->assertSame($a, $this->api->page('a'));
		$this->assertSame($aa, $this->api->page('a+aa'));

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "does-not-exist" cannot be found');
		$this->api->page('does-not-exist');
	}

	public function testPages()
	{
		$this->assertSame(['a/aa', 'a/ab'], $this->api->pages('a')->keys());
	}

	public function testPagesNotAccessible()
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

	public function testUser()
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'current@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
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

	public function testUserNotAccessible()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'name'    => 'admin',
					'options' => ['access' => ['*' => false, 'admin' => true]]
				],
				'users/editor' => [
					'name' => 'editor',
				],
			],
			'users' => [
				[
					'email' => 'current@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			],
		]);

		$app->impersonate('current@getkirby.com');
		$api = $app->api();

		$this->assertSame('current@getkirby.com', $api->user()->email());
		$this->assertSame('editor@getkirby.com', $api->user('editor@getkirby.com')->email());

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "admin@getkirby.com" cannot be found');
		$this->api->user('admin@getkirby.com');
	}

	public function testUsers()
	{
		$this->assertSame($this->app->users()->pluck('email'), $this->api->users()->pluck('email'));
	}

	public function testUsersNotAccessible()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'name'    => 'admin',
					'options' => ['access' => ['*' => false, 'admin' => true]]
				],
				'users/editor' => [
					'name' => 'editor',
				],
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
			]
		]);

		$app->impersonate('admin@getkirby.com');
		$this->assertSame(2, $app->users()->count());
		$this->assertSame(2, $app->api()->users()->count());

		$app->impersonate('editor@getkirby.com');
		$this->assertSame(2, $app->users()->count());
		$this->assertSame(1, $app->api()->users()->count());
		$this->assertSame('editor', $app->api()->users()->first()->role()->name());
	}

	public function testFileGetRoute()
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

	public function testAuthenticationWithoutCsrf()
	{
		$auth = $this->createMock(Auth::class);
		$auth->method('type')->willReturn('session');
		$auth->method('csrf')->willReturn(false);

		$kirby = $this->createMock(App::class);
		$kirby->method('auth')->willReturn($auth);

		$this->expectException(AuthException::class);
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

		$this->expectException(AuthException::class);
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
		$app->impersonate('kirby');

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

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The field could not be found');

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

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The field could not be found');

		$page = $app->page('test');
		$app->api()->fieldApi($page, '');
	}

	public function testRenderExceptionWithDebugging()
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

		$this->assertInstanceOf(Response::class, $result);
		$this->assertSame(json_encode($expected), $result->body());
	}

	public function testSectionApi()
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

	public function testSectionApiWithInvalidSection()
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

	public function testSite()
	{
		$this->assertSame($this->app->site(), $this->api->site());
	}

	public function testSiteNotAccessible()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'site' => [
					'options' => ['access' => ['*' => false, 'admin' => true]]
				],
				'users/human-resources' => [
					'name' => 'human-resources',
				],
			],
			'users' => [
				[
					'email' => 'human-resources@getkirby.com',
					'role'  => 'human-resources'
				],
			]
		]);

		$app->impersonate('human-resources@getkirby.com');

		$this->assertNull($app->api()->site());
	}
}
