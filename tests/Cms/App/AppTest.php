<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Email\PHPMailer;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Route;
use Kirby\Session\Session;
use Kirby\Toolkit\Str;
use ReflectionMethod;

/**
 * @coversDefaultClass \Kirby\Cms\App
 */
class AppTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.App';

	protected $_SERVER;

	public function setUp(): void
	{
		$this->_SERVER = $_SERVER;
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
		$_SERVER = $this->_SERVER;
	}

	/**
	 * @covers ::apply
	 */
	public function testApply()
	{
		$self = $this;

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'hooks' => [
					'noModify' => [
						function ($value) {
							// don't return anything
						},
						function ($value) {
							// explicitly return null (should be the same internally)
							return null;
						}
					],
					'singleParam' => [
						function ($event, $value) use ($self) {
							$self->assertSame(2, func_num_args());
							$self->assertSame('singleParam', $event->name());
							$self->assertSame(['value' => $value], $event->arguments());

							return $value * 2;
						},
						function ($value) {
							// don't return anything
						},
						function ($value) {
							return $value + 1;
						},
					],
					'multiParams' => [
						function ($arg2, $arg1, $value) use ($self) {
							$self->assertSame(3, func_num_args());
							$self->assertSame('Arg1', $arg1);
							$self->assertSame('Arg2', $arg2);

							return $value * 2;
						},
						function ($arg1, $value, $arg3, $arg2) use ($self) {
							$self->assertSame(4, func_num_args());
							$self->assertSame('Arg1', $arg1);
							$self->assertSame('Arg2', $arg2);
							$self->assertNull($arg3);
						},
						function ($arg1, $arg2, $value) use ($self) {
							$self->assertSame(3, func_num_args());
							$self->assertSame('Arg1', $arg1);
							$self->assertSame('Arg2', $arg2);

							return $value + 1;
						},
					]
				]
			]
		]);

		$this->assertSame(10, $app->apply('noModify', ['value' => 10], 'value'));

		$this->assertSame(5, $app->apply('singleParam', ['value' => 2], 'value'));
		$this->assertSame(21, $app->apply('singleParam', ['value' => 10], 'value'));

		$arguments = ['arg1' => 'Arg1', 'arg2' => 'Arg2', 'value' => 2];
		$this->assertSame(5, $app->apply('multiParams', $arguments, 'value'));
		$arguments['value'] = 10;
		$this->assertSame(21, $app->apply('multiParams', $arguments, 'value'));

		$this->assertSame(2, $app->apply('does-not-exist', ['value' => 2], 'value'));
	}

	/**
	 * @covers ::apply
	 */
	public function testApplyWildcard()
	{
		$self = $this;

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'hooks' => [
				'test.event:after' => [
					function ($value, $event) use ($self) {
						$self->assertSame('test.event:after', $event->name());

						return $value * 2 + 1;
					},
					function ($value) {
						return $value * 3 + 5;
					}
				],
				'test.*:after' => [
					function ($value, $event) use ($self) {
						$self->assertSame('test.event:after', $event->name());

						return $value * 2 + 7;
					}
				],
				'test.event:*' => [
					function ($value, $event) use ($self) {
						$self->assertSame('test.event:after', $event->name());

						return $value * 3 + 2;
					}
				]
			]
		]);

		$this->assertSame(143, $app->apply('test.event:after', ['value' => 2], 'value'));
	}

	/**
	 * @covers ::clone
	 */
	public function testClone()
	{
		$app = new App();
		$app->data['test'] = 'testtest';
		$this->assertSame($app, App::instance());

		$clone = $app->clone([
			'options' => ['test' => 123]
		]);
		$this->assertNotSame($app, $clone);
		$this->assertSame($clone, App::instance());
		$this->assertSame(123, $clone->option('test'));
		$this->assertSame('testtest', $clone->data['test']);

		$clone = $app->clone([
			'options' => ['test' => 123]
		], false);
		$this->assertNotSame($app, $clone);
		$this->assertNotSame($clone, App::instance());
		$this->assertSame(123, $clone->option('test'));
		$this->assertSame('testtest', $clone->data['test']);
	}

	/**
	 * @covers ::collection
	 */
	public function testCollection()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'collections' => [
				'test' => function ($pages) {
					return $pages;
				}
			]
		]);

		$collection = $app->collection('test');

		$this->assertCount(1, $collection);
		$this->assertSame('test', $collection->first()->slug());
	}

	/**
	 * @covers ::collection
	 */
	public function testCollectionWithOptions()
	{
		$test = $this;
		$app  = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'collections' => [
				// order of parameters does not matter as their values
				// are assigned by name
				'test' => function (int $a, int $c, int $b) {
					return $a + $b - $c;
				},
				'defaults' => function (int $a, int $c, int $b = 20) {
					return $a + $b - $c;
				},
				'nullable' => function ($shallBeNull) use ($test) {
					$test->assertNull($shallBeNull);
					return [];
				},
				'overwrites' => function (App $kirby) use ($test) {
					$test->assertInstanceOf(App::class, $kirby);
					return [];
				},
			]
		]);

		$collection = $app->collection(
			'test',
			['a' => 10, 'b' => 20, 'c' => 10]
		);
		$this->assertSame(20, $collection);

		// ensure that collection is not cached
		// when called with different options
		$collection = $app->collection(
			'test',
			['a' => 5, 'b' => 5, 'c' => 2]
		);
		$this->assertSame(8, $collection);

		// with defaults
		$collection = $app->collection(
			'defaults',
			['a' => 10, 'c' => 10]
		);
		$this->assertSame(20, $collection);

		// null as fallback for arguments
		$app->collection('nullable');

		// don't overwrite default objects
		$app->collection('overwrites', ['kirby' => 'foo']);
	}

	/**
	 * @covers ::contentToken
	 */
	public function testContentToken()
	{
		// without configured salt
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
		$this->assertSame(hash_hmac('sha1', 'test', '/dev/null/content'), $app->contentToken('model', 'test'));
		$this->assertSame(hash_hmac('sha1', 'test', '/dev/null'), $app->contentToken($app, 'test'));

		// with custom static salt
		$app = new App([
			'options' => [
				'content.salt' => 'salt and pepper and chili'
			]
		]);
		$this->assertSame(hash_hmac('sha1', 'test', 'salt and pepper and chili'), $app->contentToken('model', 'test'));

		// with callback
		$app = new App([
			'options' => [
				'content.salt' => function ($model) {
					return 'salt ' . $model;
				}
			]
		]);
		$this->assertSame(hash_hmac('sha1', 'test', 'salt lake city'), $app->contentToken('lake city', 'test'));
	}

	/**
	 * @covers ::csrf
	 */
	public function testCsrf()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
				'sessions' => static::TMP,
			]
		]);

		$session = $app->session();

		// should generate token
		$session->remove('kirby.csrf');
		$token = $app->csrf();
		$this->assertIsString($token);
		$this->assertStringMatchesFormat('%x', $token);
		$this->assertSame(64, strlen($token));
		$this->assertSame($session->get('kirby.csrf'), $token);

		// should not regenerate when a param is passed
		$this->assertFalse($app->csrf(null));
		$this->assertFalse($app->csrf(false));
		$this->assertFalse($app->csrf(123));
		$this->assertFalse($app->csrf('some invalid string'));
		$this->assertSame($token, $session->get('kirby.csrf'));

		// should not regenerate if there is already a token
		$token2 = $app->csrf();
		$this->assertSame($token, $token2);

		// should regenerate if there is an invalid token
		$session->set('kirby.csrf', 123);
		$token3 = $app->csrf();
		$this->assertNotEquals($token, $token3);
		$this->assertSame(64, strlen($token3));
		$this->assertSame($session->get('kirby.csrf'), $token3);

		// should verify token
		$this->assertTrue($app->csrf($token3));
		$this->assertFalse($app->csrf($token2));
		$this->assertFalse($app->csrf(null));
		$this->assertFalse($app->csrf(false));
		$this->assertFalse($app->csrf(123));
		$this->assertFalse($app->csrf('some invalid string'));

		$session->destroy();
	}

	public function testDebugInfo()
	{
		$app = new App();
		$debuginfo = $app->__debugInfo();

		$this->assertArrayHasKey('languages', $debuginfo);
		$this->assertArrayHasKey('options', $debuginfo);
		$this->assertArrayHasKey('request', $debuginfo);
		$this->assertArrayHasKey('roots', $debuginfo);
		$this->assertArrayHasKey('site', $debuginfo);
		$this->assertArrayHasKey('urls', $debuginfo);
		$this->assertArrayHasKey('version', $debuginfo);
	}

	public function testDefaultRoles()
	{
		$app = new App([
			'roots' => [
				'site' => __DIR__ . '/does-not-exist'
			]
		]);

		$this->assertInstanceOf(Roles::class, $app->roles());
	}

	public function testEmail()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$email = $app->email(
			[
				'from'    => 'test@getkirby.com',
				'to'      => 'test@getkirby.com',
				'body'    => 'test',
				'subject' => 'Test'
			],
			[
				'debug'   => true
			]
		);

		$this->assertInstanceOf(PHPMailer::class, $email);
	}

	/**
	 * @covers ::environment
	 */
	public function testEnvironment()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'server' => $info = [
				'foo' => 'bar'
			]
		]);

		$this->assertSame($info, $app->environment()->info());
	}

	/**
	 * @covers ::environment
	 */
	public function testEnvironmentBeforeInitialization()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The environment is not allowed');

		new App([
			'options' => [
				'debug' => true,
				'url'   => ['https://getkirby.com', 'https://trykirby.com']
			]
		]);
	}

	/**
	 * @covers ::image
	 */
	public function testImage()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'files' => [
					['filename' => 'sitefile.jpg']
				],
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'pagefile.jpg']
						]
					]
				]
			]
		]);

		$image = $app->image('test/pagefile.jpg');
		$this->assertIsFile($image);

		$image = $app->image('/sitefile.jpg');
		$this->assertIsFile($image);

		// get the first image of the current page
		$app->site()->visit('test');
		$image = $app->image();
		$this->assertIsFile($image);

		$image = $app->image('pagefile.jpg');
		$this->assertIsFile($image);

		$image = $app->image('does-not-exist.jpg');
		$this->assertNull($image);
	}

	/**
	 * @covers ::models
	 */
	public function testModels()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'files' => [
					['filename' => 'sitefile.jpg']
				],
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'pagefile.jpg']
						]
					]
				]
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'files' => [
						['filename' => 'userfile.jpg'],
					]
				]
			]
		]);

		$models = $app->models();

		$this->assertSame('sitefile.jpg', $models->current()->filename());
		$models->next();
		$this->assertIsSite($models->current());
		$models->next();
		$this->assertSame('pagefile.jpg', $models->current()->filename());
		$models->next();
		$this->assertSame('test', $models->current()->slug());
		$models->next();
		$this->assertSame('userfile.jpg', $models->current()->filename());
		$models->next();
		$this->assertSame('test@getkirby.com', $models->current()->email());
	}

	/**
	 * @covers ::nonce
	 */
	public function testNonce()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->assertIsString($nonce = $app->nonce());
	}

	public function testOption()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'foo' => 'bar'
			]
		]);

		$this->assertSame('bar', $app->option('foo'));
	}

	public function testOptionWithDotNotation()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'mother' => [
					'child' => 'test'
				]
			]
		]);

		$this->assertSame('test', $app->option('mother.child'));
	}

	public function testOptionFromPlugin()
	{
		App::destroy();
		App::plugin('namespace/plugin', [
			'options' => [
				'key' => 'A',
				'nested' => [
					'key'     => 'B',
					'another' => 'C'
				],
				'another' => 'D',
				'foo'     => 'bar'
			]
		]);

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'namespace.plugin' => [
					'key' => 'A1'
				],

				// legacy syntax (<= Kirby 3.4)
				'namespace.plugin.nested' => [
					'key' => 'B1'
				],
				'namespace.plugin.another' => 'D1'
			]
		]);

		$this->assertSame([
			'key' => 'A1',
			'nested' => [
				'key'     => 'B1',
				'another' => 'C',
			],
			'another' => 'D1',
			'foo'     => 'bar'
		], $app->option('namespace.plugin'));
		$this->assertSame('B1', $app->option('namespace.plugin.nested')['key']);
	}

	public function testOptions()
	{
		App::destroy();

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => $options = [
				'a' => 'A',
				'b' => 'B',

				// option that could be from a plugin but isn't
				'a.b.c' => 'test'
			]
		]);

		$this->assertSame($options, $app->options());
	}

	public function testOptionsFromFile()
	{
		App::destroy();

		$app = new App([
			'roots' => [
				'index'  => '/dev/null',
				'config' => static::FIXTURES . '/AppTest/options'
			],
			'server' => [
				'SERVER_NAME' => 'getkirby.com',
				'SERVER_ADDR' => '10.1.2.3',
				'HTTPS'       => true
			]
		]);

		$this->assertSame([
			'option1' => 'global',
			'option2' => 'getkirby',
			'url'     => 'https://getkirby.com/docs',
			'option3' => '10.1.2.3',
			'option4' => '10.1.2.3'
		], $app->options());
	}

	public function testOptionsFromFileWithEnv1()
	{
		App::destroy();

		$app = new App([
			'roots' => [
				'index'  => '/dev/null',
				'config' => static::FIXTURES . '/AppTest/options-env1'
			],
			'server' => [
				'SERVER_NAME' => 'getkirby.com',
				'SERVER_ADDR' => '10.1.2.3',
				'HTTPS'       => true
			]
		]);

		$this->assertSame([
			'option1' => 'global',
			'option2' => 'getkirby',
			'url'     => 'https://getkirby.com/docs',
			'option3' => '10.1.2.3',
			'option4' => 'env',
			'option5' => 'env'
		], $app->options());
	}

	public function testOptionsFromFileWithEnv2()
	{
		App::destroy();

		$app = new App([
			'roots' => [
				'index'  => '/dev/null',
				'config' => static::FIXTURES . '/AppTest/options-env2'
			],
			'server' => [
				'SERVER_NAME' => 'getkirby.com',
				'SERVER_ADDR' => '10.1.2.3',
				'HTTPS'       => true
			]
		]);

		// the env file determines the allowed base URL
		// and therefore the loaded host-specific config file
		$this->assertSame([
			'option1' => 'global',
			'option2' => 'trykirby',
			'url'     => 'https://trykirby.com',
			'option3' => '10.1.2.3',
			'option4' => 'env',
			'option5' => 'env'
		], $app->options());
	}

	public function testOptionsOnReady()
	{
		App::destroy();

		$app = new App([
			'cli' => false,
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'content' => [
					'home'  => 'test',
					'error' => 'another-test'
				]
			],
			'options' => [
				'ready' => $ready = function ($kirby) {
					return [
						'test'         => $kirby->root('index'),
						'another.test' => 'foo',
						'debug'        => true,
						'home'         => $kirby->site()->content()->home()->value(),
						'error'        => $kirby->site()->content()->error()->value(),
						'slugs'        => 'de'
					];
				},
				'whoops' => true
			]
		]);

		$this->assertSame([
			'ready' => $ready,
			'whoops' => true,
			'test' => '/dev/null',
			'another.test' => 'foo',
			'debug' => true,
			'home' => 'test',
			'error' => 'another-test',
			'slugs' => 'de'
		], $app->options());

		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');
		$whoopsMethod->setAccessible(true);
		$whoopsHandler = $whoopsMethod->invoke($app)->getHandlers()[0];
		$this->assertInstanceOf('Whoops\Handler\PrettyPageHandler', $whoopsHandler);

		$this->assertSame('test', $app->site()->homePageId());
		$this->assertSame('another-test', $app->site()->errorPageId());

		$this->assertSame('ss', Str::$language['ß']);
	}

	public function testRolesFromFixtures()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
				'site' => static::FIXTURES
			]
		]);

		$this->assertInstanceOf(Roles::class, $app->roles());
	}

	// TODO: debug is not working properly
	// public function testEmail()
	// {
	//     $app = new App();
	//     $email = $app->email([
	//         'from' => 'no-reply@supercompany.com',
	//         'to' => 'someone@gmail.com',
	//         'subject' => 'Thank you for your contact request',
	//         'body' => 'We will never reply',
	//         'debug' => true
	//     ]);
	//     $this->assertInstanceOf(\Kirby\Email\Email::class, $email);
	// }

	public function testRoute()
	{
		$app = new App([
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
					]
				]
			]
		]);

		$response = $app->call('projects');
		$route    = $app->route();

		$this->assertIsPage($response);
		$this->assertInstanceOf(Route::class, $route);
	}

	public function testSession()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
				'sessions' => static::TMP,
			]
		]);

		$this->assertTrue($app->response()->cache());
		$this->assertSame([], $app->response()->headers());

		$this->assertInstanceOf(Session::class, $app->session());

		$this->assertTrue($app->response()->cache());
		$this->assertSame(['Vary' => 'Cookie'], $app->response()->headers());

		// manual session that blocks caching
		$app->response()->headers([]);
		$this->assertInstanceOf(Session::class, $app->session(['createMode' => 'manual']));
		$this->assertFalse($app->response()->cache());
		$this->assertSame(['Vary' => 'Cookie', 'Cache-Control' => 'no-store, private'], $app->response()->headers());

		// test lazy header setter
		$app->response()->headers(['Cache-Control' => 'custom']);
		$this->assertInstanceOf(Session::class, $app->session(['createMode' => 'manual']));
		$this->assertFalse($app->response()->cache());
		$this->assertSame(['Vary' => 'Cookie', 'Cache-Control' => 'custom'], $app->response()->headers());
	}

	public function testInstance()
	{
		App::destroy();
		$this->assertNull(App::instance(null, true));

		$instance1 = new App();
		$this->assertSame($instance1, App::instance());

		$instance2 = new App();
		$this->assertSame($instance2, App::instance());
		$this->assertSame($instance1, App::instance($instance1));
		$this->assertSame($instance1, App::instance());

		$instance3 = new App([], false);
		$this->assertSame($instance1, App::instance());
		$this->assertNotSame($instance3, App::instance());
	}

	public function testFindPageFile()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'test-a.jpg']
						]
					],
				]
			]
		]);

		$page  = $app->page('test');
		$fileA = $page->file('test-a.jpg');
		$fileB = $page->file('test-b.jpg');

		// plain
		$this->assertSame($fileA, $app->file('test/test-a.jpg'));

		// with page parent
		$this->assertSame($fileA, $app->file('test-a.jpg', $page));

		// with file parent
		$this->assertSame($fileB, $app->file('test-b.jpg', $fileA));
	}

	public function testFindSiteFile()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'files' => [
					['filename' => 'test-a.jpg'],
					['filename' => 'test-b.jpg']
				],
				'children' => [
					[
						'slug'  => 'home',
						'files' => [
							['filename' => 'test-c.jpg']
						]
					],
				]
			]
		]);

		$site  = $app->site();
		$page  = $site->find('home');
		$fileA = $site->file('test-a.jpg');
		$fileB = $site->file('test-b.jpg');
		$fileC = $page->file('test-c.jpg');

		// plain
		$this->assertSame($fileA, $app->file('test-a.jpg'));

		// with page parent
		$this->assertSame($fileA, $app->file('test-a.jpg', $site));

		// with subpage parent
		$this->assertSame($fileC, $app->file('home/test-c.jpg'));
		$this->assertSame($fileC, $app->file('home/test-c.jpg', $site));
		$this->assertSame($fileC, $app->file('test-c.jpg', $page));

		// with file parent
		$this->assertSame($fileB, $app->file('test-b.jpg', $fileA));
		$this->assertSame($fileC, $app->file('test-c.jpg', $fileC));
	}

	public function testFindUserFile()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'files' => [
						['filename' => 'test-a.jpg'],
						['filename' => 'test-b.jpg']
					]
				]
			]
		]);

		$user  = $app->user('test@getkirby.com');
		$fileA = $user->file('test-a.jpg');
		$fileB = $user->file('test-b.jpg');

		// with user parent
		$this->assertSame($fileA, $app->file('test-a.jpg', $user));

		// with file parent
		$this->assertSame($fileB, $app->file('test-b.jpg', $fileA));
	}

	/**
	 * @covers ::file
	 */
	public function testFindFileByUUID()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							[
								'filename' => 'test-a.jpg',
								'content'  => ['uuid' => 'my-file']
							]
						]
					],
				]
			]
		]);

		$page = $app->page('test');
		$file = $page->file('test-a.jpg');

		$this->assertIsFile($file, $app->file('file://my-file'));
	}

	public function testBlueprints()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
				'blueprints' => static::TMP,
			],
			'blueprints' => [
				'pages/a' => ['title' => 'A'],
				'pages/d' => ['title' => 'C'],
				'files/a' => ['title' => 'File A']
			]
		]);

		Data::write(static::TMP . '/pages/b.yml', ['title' => 'B']);
		Data::write(static::TMP . '/pages/c.yml', ['title' => 'C']);
		Data::write(static::TMP . '/files/b.yml', ['title' => 'File B']);

		$expected = [
			'a',
			'b',
			'c',
			'd',
			'default'
		];

		$this->assertSame($expected, $app->blueprints());

		$expected = [
			'a',
			'b',
			'default'
		];

		$this->assertSame($expected, $app->blueprints('files'));
	}

	/**
	 * @covers ::trigger
	 */
	public function testTrigger()
	{
		$self  = $this;
		$count = 0;

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'hooks' => [
					'simple' => [
						function ($arg) use ($self, &$count) {
							$self->assertSame(1, func_num_args());

							$count += $arg;
						}
					],
					'multiple' => [
						function ($arg) use ($self, &$count) {
							$self->assertSame(1, func_num_args());

							$count = $count * 2 + $arg;
						},
						function ($arg) use ($self, &$count) {
							$self->assertSame(1, func_num_args());

							$count = $count * 3 + $arg * 2;
						}
					],
					'arguments' => [
						function ($arg2, $arg1, $arg3, $event) use ($self, &$count) {
							$self->assertSame(4, func_num_args());
							$self->assertSame('Arg1', $arg1);
							$self->assertSame('Arg2', $arg2);
							$self->assertNull($arg3);
							$self->assertSame('arguments', $event->name());
							$self->assertSame(['arg1' => 'Arg1', 'arg2' => 'Arg2'], $event->arguments());

							$count++;
						}
					],
					'recursive1' => [
						function () use ($self, &$count) {
							$self->assertSame(0, func_num_args());

							$count += 5;

							$this->trigger('recursive3');
							if ($count < 50) { // prevent too much recursion
								$this->trigger('recursive2');
							}
						},
					],
					'recursive2' => [
						function () use ($self, &$count) {
							$self->assertSame(0, func_num_args());

							$count = $count * 2 + 1;

							if ($count < 50) { // prevent too much recursion
								$this->trigger('recursive1');
							}
						}
					],
					'recursive3' => [
						function () use ($self, &$count) {
							$self->assertSame(0, func_num_args());

							$count += 4;
						}
					]
				]
			]
		]);

		// simple test
		$count = 0;
		$app->trigger('simple', ['arg' => 2]);
		$this->assertSame(2, $count);
		$app->trigger('simple', ['arg' => 3]);
		$this->assertSame(5, $count);

		// multiple hooks get run in the correct order
		$count = 0;
		$app->trigger('multiple', ['arg' => 2]);
		$this->assertSame(10, $count);

		// ensure that the correct arguments get passed in the right order
		$count = 0;
		$app->trigger('arguments', ['arg1' => 'Arg1', 'arg2' => 'Arg2']);
		$this->assertSame(1, $count);

		// each hook should only be called once
		$count = 0;
		$app->trigger('recursive1');
		$this->assertSame(19, $count);

		// but in a separate run each hook should be triggered again
		$count = 0;
		$app->trigger('recursive1');
		$this->assertSame(19, $count);

		// hooks get called in the correct order
		$count = 0;
		$app->trigger('recursive2');
		$this->assertSame(10, $count);
	}

	/**
	 * @covers ::trigger
	 */
	public function testTriggerWildcard()
	{
		$self  = $this;
		$count = 0;

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'hooks' => [
				'test.event:after' => [
					function ($event) use ($self, &$count) {
						$self->assertSame('test.event:after', $event->name());

						$count = $count * 2 + 1;
					},
					function () use (&$count) {
						$count = $count * 3 + 5;
					}
				],
				'test.*:after' => [
					function ($event) use ($self, &$count) {
						$self->assertSame('test.event:after', $event->name());

						$count = $count * 2 + 7;
					}
				],
				'test.event:*' => [
					function ($event) use ($self, &$count) {
						$self->assertSame('test.event:after', $event->name());

						$count = $count * 3 + 2;
					}
				]
			]
		]);

		// hooks get called in the correct order
		$count = 2;
		$app->trigger('test.event:after');
		$this->assertSame(143, $count);
	}

	public static function urlProvider(): array
	{
		return [
			['http://getkirby.com', 'http://getkirby.com'],
			['https://getkirby.com', 'https://getkirby.com'],
			['https://getkirby.com/test', 'https://getkirby.com/test'],
			['/', '/'],
			['/test', '/test'],
		];
	}

	/**
	 * @dataProvider urlProvider
	 */
	public function testUrl($url, $expected)
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'url' => $url
			]
		]);

		$this->assertSame($url, $app->url('index'));
		$this->assertSame($expected, $app->url('index', true)->toString());

		// reset SERVER_ADDR
		$_SERVER['SERVER_ADDR'] = null;
	}

	public function testUrlFromEnvWithDetection()
	{
		App::destroy();

		$app = new App([
			'roots' => [
				'index'  => '/dev/null',
				'config' => static::FIXTURES . '/AppTest/options'
			],
			'server' => [
				'SERVER_NAME' => 'trykirby.com',
				'HTTPS'       => true
			]
		]);

		$this->assertSame(['https://getkirby.com', 'https://trykirby.com'], $app->option('url'));
		$this->assertSame('https://trykirby.com', $app->url('index'));
		$this->assertSame('https://trykirby.com/panel', $app->url('panel'));
	}

	public function testUrlFromEnvWithOverride()
	{
		App::destroy();

		$app = new App([
			'roots' => [
				'index'  => '/dev/null',
				'config' => static::FIXTURES . '/AppTest/options'
			],
			'server' => [
				'SERVER_NAME' => 'getkirby.com',
				'HTTPS'       => true
			]
		]);

		$this->assertSame('https://getkirby.com/docs', $app->option('url'));
		$this->assertSame('https://getkirby.com/docs', $app->url('index'));
		$this->assertSame('https://getkirby.com/docs/panel', $app->url('panel'));
	}

	public function testVersionHash()
	{
		$this->assertSame(md5(App::version()), App::versionHash());
	}

	public function testSlugsOption()
	{
		// string option
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'slugs' => 'fr'
			]
		]);

		$this->assertSame(['slugs' => 'fr'], $app->options());
		$this->assertSame('fr', $app->option('slugs'));
		$this->assertSame('AE', Str::$language['Æ']);

		// array option
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'slugs' => [
					'language' => 'de'
				]
			]
		]);

		$this->assertSame([
			'slugs' => [
				'language' => 'de'
			]
		], $app->options());
		$this->assertSame(['language' => 'de'], $app->option('slugs'));
		$this->assertSame('ss', Str::$language['ß']);
	}

	/**
	 * @covers ::controller
	 * @covers ::controllerLookup
	 */
	public function testController()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
				'controllers' => static::FIXTURES . '/controllers'
			]
		]);

		Page::factory([
			'slug' => 'test',
			'template' => 'test'
		]);

		$this->assertSame(['foo' => 'bar'], $app->controller('test'));
	}

	/**
	 * @covers ::controller
	 * @covers ::controllerLookup
	 */
	public function testControllerCallback()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'controllers' => [
				'test' => function () {
					return ['foo' => 'bar'];
				}
			]
		]);

		Page::factory([
			'slug' => 'test',
			'template' => 'test'
		]);

		$this->assertSame(['foo' => 'bar'], $app->controller('test'));
	}

	/**
	 * @covers ::controller
	 * @covers ::controllerLookup
	 */
	public function testControllerRepresentation()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
				'controllers' => static::FIXTURES . '/controllers'
			]
		]);

		Page::factory([
			'slug' => 'test',
			'template' => 'another'
		]);

		ob_start();
		$app->controller('another.json', [], 'json');
		$response = ob_get_clean();

		$this->assertSame('{"foo":"bar"}', $response);
	}

	/**
	 * @covers ::controller
	 * @covers ::controllerLookup
	 */
	public function testControllerHtmlRepresentation()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
				'controllers' => static::FIXTURES . '/controllers'
			]
		]);

		Page::factory([
			'slug' => 'test',
			'template' => 'foo'
		]);

		$this->assertSame(['foo' => 'bar'], $app->controller('test', [], 'json'));
	}

	/**
	 * @covers ::controller
	 * @covers ::controllerLookup
	 */
	public function testControllerFallbackRepresentation()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
				'controllers' => static::FIXTURES . '/controllers'
			]
		]);

		Page::factory([
			'slug' => 'test',
			'template' => 'none'
		]);

		$this->assertSame(['title' => 'Site'], $app->controller('none', [], 'json'));
	}

	/**
	 * @covers ::path
	 */
	public function testPath()
	{
		$app = new App();
		$this->assertSame('', $app->path());

		// with custom request
		$app = new App([
			'request' => [
				'url' => [
					'path' => '/foo/bar'
				]
			]
		]);

		$this->assertSame('foo/bar', $app->path());

		// from request uri
		$app = new App([
			'server' => [
				'REQUEST_URI' => '/foo/bar'
			]
		]);

		$this->assertSame('foo/bar', $app->path());

		// with params
		$app = new App([
			'request' => [
				'url' => [
					'path' => '/foo/bar/page:1'
				]
			]
		]);

		$this->assertSame('foo/bar', $app->path());
	}

	/**
	 * @covers ::page
	 */
	public function testPageWithUUID()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'content'  => ['uuid' => 'my-page']
					],
				]
			]
		]);

		$page = $app->page('test');
		$this->assertIsPage($page, $app->page('page://my-page'));
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP,
				'templates' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'home',
					]
				]
			]
		]);

		F::write(static::TMP . '/default.php', 'Hello');

		$this->assertSame('Hello', $app->render()->body());

		$_ENV['KIRBY_RENDER'] = false;
		$this->assertNull($app->render());

		$_ENV['KIRBY_RENDER'] = '0';
		$this->assertNull($app->render());

		$_ENV['KIRBY_RENDER'] = 'false';
		$this->assertNull($app->render());

		$_ENV['KIRBY_RENDER'] = '1';
		$this->assertNotNull($app->render());

		$_ENV['KIRBY_RENDER'] = 'true';
		$this->assertNotNull($app->render());

		unset($_ENV['KIRBY_RENDER']);
	}
}
