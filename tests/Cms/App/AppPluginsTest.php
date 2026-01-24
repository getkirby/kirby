<?php

namespace Kirby\Cms;

use Kirby\Auth\Method;
use Kirby\Auth\Status;
use Kirby\Cache\FileCache;
use Kirby\Cms\Auth\Challenge;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Filesystem\Mime;
use Kirby\Form\Field as FormField;
use Kirby\Image\Image;
use Kirby\Plugin\Plugin;
use Kirby\Tests\MockTime;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

class DummyAuthChallenge extends Challenge
{
	public static function isAvailable(User $user, string $mode): bool
	{
		return true;
	}

	public static function create(User $user, array $options): string|null
	{
		return 'test';
	}

	public static function verify(User $user, string $code): bool
	{
		return $code === 'test-verify';
	}
}

class DummyAuthMethod extends Method
{
	public function authenticate(
		string $email,
		string|null $password = null,
		bool $long = false
	): Status|User {
		return new User(['email' => $email]);
	}
}

class DummyCache extends FileCache
{
}

class DummyFile extends File
{
}

class DummyPage extends Page
{
}

class DummyUser extends User
{
}

class DummyFilePreview
{
}

#[CoversClass(App::class)]
class AppPluginsTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.AppPlugins';

	// used for testPluginLoader()
	public static bool $calledPluginsLoadedHook = false;

	public function testApi(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'api' => [
				'routes' => [
					[
						'pattern' => 'awesome',
						'action'  => fn () => 'nice'
					]
				],
				'authentication' => fn () => true
			]
		]);

		$kirby->impersonate('kirby');
		$this->assertSame('nice', $kirby->call('api/awesome'));
	}

	public function testApiRoutePlugins(): void
	{
		App::plugin('test/a', [
			'api' => [
				'routes' => [
					[
						'pattern' => 'a',
						'action'  => fn () => 'a'
					]
				]
			]
		]);

		App::plugin('test/b', [
			'api' => [
				'routes' => [
					[
						'pattern' => 'b',
						'action'  => fn () => 'b'
					]
				]
			]
		]);

		App::plugin('test/c', [
			'api' => [
				'routes' => [
					[
						'pattern' => 'c',
						'action'  => fn () => 'c'
					]
				]
			]
		]);

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'api' => [
				'authentication' => fn () => true
			],
		]);

		$app->impersonate('kirby');

		$this->assertSame('a', $app->api()->call('a'));
		$this->assertSame('b', $app->api()->call('b'));
		$this->assertSame('c', $app->api()->call('c'));
	}

	public function testApiRouteCallbackPlugins(): void
	{
		App::plugin('test/a', [
			'api' => [
				'routes' => fn ($kirby) => [
					[
						'pattern' => 'a',
						'action'  => fn () => $kirby->root('index')
					]
				]
			]
		]);

		App::plugin('test/b', [
			'api' => [
				'routes' => fn ($kirby) => [
					[
						'pattern' => 'b',
						'action'  => fn () => 'b'
					]
				]
			]
		]);

		App::plugin('test/c', [
			'api' => [
				'routes' => [
					[
						'pattern' => 'c',
						'action'  => fn () => 'c'
					]
				]
			]
		]);

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'api' => [
				'authentication' => fn () => true
			],
		]);

		$app->impersonate('kirby');

		$this->assertSame('/dev/null', $app->api()->call('a'));
		$this->assertSame('b', $app->api()->call('b'));
		$this->assertSame('c', $app->api()->call('c'));
	}

	public function testApiRouteCallbackPluginWithOptionAccess(): void
	{
		App::plugin('your/plugin', [
			'options' => [
				'test' => 'Test'
			],
			'api' => [
				'routes' => fn ($kirby) => [
					[
						'pattern' => 'test',
						'action'  => fn () => $kirby->option('your.plugin.test')
					]
				]
			]
		]);

		$app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roots' => [
				'index' => '/dev/null'
			],
		]);

		$app->impersonate('kirby');
		$this->assertSame('Test', $app->api()->call('test'));
	}

	public function testAuthChallenge(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => static::TMP
			],
			'authChallenges' => [
				'dummy' => DummyAuthChallenge::class
			],
			'options' => [
				'auth.challenges' => ['dummy']
			],
			'users' => [
				[
					'email' => 'homer@simpsons.com'
				]
			]
		]);
		$auth    = $kirby->auth();
		$session = $kirby->session();

		$status = $auth->createChallenge('homer@simpsons.com');
		$this->assertSame([
			'challenge' => 'dummy',
			'data'      => null,
			'email'     => 'homer@simpsons.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('dummy', $status->challenge(false));
		$this->assertSame('homer@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertSame('dummy', $session->get('kirby.challenge.type'));
		$this->assertTrue(password_verify('test', $session->get('kirby.challenge.code')));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));

		$this->assertSame(
			$kirby->user('homer@simpsons.com'),
			$auth->verifyChallenge('test-verify')
		);

		$kirby->session()->destroy();
	}

	public function testAuthMethod(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => static::TMP
			],
			'authMethods' => [
				'dummy' => DummyAuthMethod::class
			],
			'options' => [
				'auth.methods' => ['dummy']
			]
		]);

		$auth   = $kirby->auth();
		$method = $auth->methods()->get('dummy');
		$user   = $method->authenticate('lisa@simpson.com');
		$this->assertInstanceOf(User::class, $user);
		$this->assertSame('lisa@simpson.com', $user->email());
	}

	public function testBlueprint(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/test' => $file = 'test.yml'
			]
		]);

		$this->assertSame($file, $kirby->extension('blueprints', 'pages/test'));
	}

	public function testCacheType(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => static::TMP
			],
			'cacheTypes' => [
				'file' => DummyCache::class
			],
			'options' => [
				'cache' => [
					'pages' => true
				]
			]
		]);

		$this->assertInstanceOf(DummyCache::class, $kirby->cache('pages'));
	}

	public function testCollection(): void
	{
		$pages = new Pages([
			$page = new Page(['slug' => 'a', 'num' => 1])
		]);

		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'collections' => [
				'test' => fn () => $pages
			],
		]);

		$this->assertIsPage($page, $kirby->collection('test')->first());
	}

	public function testCollectionFilters(): void
	{
		// fetch all previous filters
		$prevFilters = Collection::$filters;

		Collection::$filters = [];

		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'collectionFilters' => [
				'**' => $filter = [
					'validator' => fn ($value, $test) => $value === 'foo'
				]
			]
		]);

		$this->assertSame(Collection::$filters['**'], $filter);

		// restore previous filters
		Collection::$filters = $prevFilters;
	}

	public function testCommands(): void
	{
		$pages = new Pages([]);
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'commands' => [
				'test' => $command = [
					'command' => function () {
					}
				]
			],
		]);

		$this->assertSame($command, $kirby->extension('commands', 'test'));
	}

	public function testController(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'controllers' => [
				'test' => fn () => ['foo' => 'bar']
			]
		]);

		$this->assertSame(['foo' => 'bar'], $kirby->controller('test'));
	}

	public function testFieldMethod(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'fieldMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->customField()->test());

		// reset methods
		Field::$methods = [];
	}

	public function testField(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'fields' => [
				'dummy' => static::FIXTURES . '/fields/DummyField.php'
			]
		]);

		$page  = new Page(['slug' => 'test']);
		$field = new FormField('dummy', [
			'name'  => 'dummy',
			'peter' => 'shaw',
			'model' => $page
		]);

		$this->assertInstanceOf(FormField::class, $field);
		$this->assertSame('simpson', $field->homer());
		$this->assertSame('shaw', $field->peter());
	}

	public function testFilePreviews(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'filePreviews' => [
				DummyFilePreview::class
			]
		]);

		$this->assertCount(5, $app->extensions('filePreviews'));
	}

	public function testKirbyTag(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'tags' => [
				'test' => [
					'html' => fn () => 'test'
				],
				'FoO' => [
					'html' => fn () => 'test'
				]
			]
		]);

		$this->assertSame('test', $kirby->kirbytags('(test: foo)'));
		$this->assertSame('test', $kirby->kirbytags('(TEST: foo)'));

		$this->assertSame('test', $kirby->kirbytags('(foo: bar)'));
		$this->assertSame('test', $kirby->kirbytags('(FOO: bar)'));
	}

	public function testPageMethod(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'pageMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->test());

		// reset methods
		Page::$methods = [];
	}

	public function testPagesMethod(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'pagesMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$pages = new Pages([]);
		$this->assertSame('test', $pages->test());

		// reset methods
		Pages::$methods = [];
	}

	public function testPageModel(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'pageModels' => [
				'dummy' => DummyPage::class
			]
		]);

		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(DummyPage::class, $page);
	}

	public function testPageModelFromFolder(): void
	{
		$kirby = new App([
			'roots' => [
				'index'  => '/dev/null',
				'models' => static::FIXTURES . '/models'
			]
		]);

		$page = Page::factory([
			'slug' => 'test',
			'model' => 'test'
		]);

		$this->assertInstanceOf('TestPage', $page);
	}

	public function testPermission(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'permissions' => [
				'test-category' => [
					'test-action' => true,
					'another'     => false
				]
			]
		]);

		$permissions = new Permissions([]);
		$this->assertTrue($permissions->for('test-category', 'test-action'));
		$this->assertFalse($permissions->for('test-category', 'another'));

		// reset actions
		Permissions::$extendedActions = [];
	}

	public function testPermissionPlugin(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$plugin = new Plugin('kirby/manual', [
			'permissions' => [
				'test-action' => true,
				'another'     => false
			]
		]);

		$kirby->extend($plugin->extends(), $plugin);

		$permissions = new Permissions([]);
		$this->assertTrue($permissions->for('kirby.manual', 'test-action'));
		$this->assertFalse($permissions->for('kirby.manual', 'another'));

		// reset actions
		Permissions::$extendedActions = [];
	}

	public function testOption(): void
	{
		// simple
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'testOption' => 'testValue'
			]
		]);

		$this->assertSame('testValue', $kirby->option('testOption'));
	}

	public function testExtensionsFromFolders(): void
	{
		Page::$models = [];
		Dir::copy(static::FIXTURES . '/AppPluginsTest', static::TMP);

		$kirby = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$expected = [
			'regular' => 'regularPage',
			'with.dot' => 'withdotPage',
			'with-dash' => 'withdashPage',
			'with_underscore' => 'withunderscorePage'
		];

		$this->assertEquals($expected, Page::$models); // cannot use strict assertion (filesystem sorting)
	}

	public function testExtensionsFromOptions(): void
	{
		$calledRoute = false;
		$calledHook  = false;

		$kirby = new App([
			'options' => [
				'routes' => [
					[
						'pattern' => 'test',
						'action'  => function () use (&$calledRoute) {
							$calledRoute = true;
						}
					]
				],
				'hooks' => [
					'type.action:state' => function () use (&$calledHook) {
						$calledHook = true;
					}
				]
			]
		]);

		$kirby->call('test');
		$kirby->trigger('type.action:state');
		$this->assertTrue($calledRoute);
		$this->assertTrue($calledHook);
	}

	public function testPluginOptions(): void
	{
		App::plugin('test/plugin', [
			'options' => [
				'foo' => 'bar'
			]
		]);

		// simple
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'test.plugin' => [
					'foo' => 'another-bar'
				]
			]
		]);

		$this->assertSame('another-bar', $kirby->option('test.plugin.foo'));
		$this->assertSame(['foo' => 'another-bar'], $kirby->option('test.plugin'));
	}

	public function testPluginOptionsWithNonAssociativeArray(): void
	{
		// non-associative
		App::plugin('test/plugin', [
			'options' => [
				'foo' => ['one', 'two']
			]
		]);

		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'test.plugin' => [
					'foo' => ['three']
				]
			]
		]);

		$this->assertSame(['three'], $kirby->option('test.plugin.foo'));
	}

	public function testPluginOptionsWithAssociativeArray(): void
	{
		// associative
		App::plugin('test/plugin', [
			'options' => [
				'foo' => [
					'a' => 'A',
					'b' => 'B'
				]
			]
		]);

		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'test.plugin' => [
					'foo' => [
						'a' => 'Custom A'
					]
				]
			]
		]);

		$this->assertSame(['a' => 'Custom A', 'b' => 'B'], $kirby->option('test.plugin.foo'));
	}

	public function testRoutes(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'routes' => [
				[
					'pattern' => 'test',
					'action'  => fn () => 'test'
				]
			]
		]);

		$this->assertSame('test', $kirby->call('test'));
	}

	public function testRoutesCallback(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'routes' => fn () => [
				[
					'pattern' => 'test',
					'action'  => fn () => 'test'
				]
			]
		]);

		$this->assertSame('test', $kirby->call('test'));
	}

	public function testSnippet(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'snippets' => [
				'header' => $file = 'header.php'
			]
		]);

		$this->assertSame($file, $kirby->extension('snippets', 'header'));
	}

	public function testTemplate(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'templates' => [
				'project' => $file = 'project.php'
			]
		]);

		$this->assertSame($file, $kirby->extension('templates', 'project'));
	}

	public function testTranslation(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'translations' => [
				'en' => [
					'test' => 'English Test'
				],
				'de' => [
					'test' => 'Deutscher Test'
				]
			]
		]);

		I18n::$locale = 'en';

		$this->assertSame('English Test', I18n::translate('test'));

		I18n::$locale = 'de';

		$this->assertSame('Deutscher Test', I18n::translate('test'));
	}

	public function testTranslationsInPlugin(): void
	{
		App::plugin('test/test', [
			'translations' => [
				'en' => [
					'test' => 'English Test'
				],
				'de' => [
					'test' => 'Deutscher Test'
				]
			]
		]);

		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		I18n::$locale = 'en';

		$this->assertSame('English Test', I18n::translate('test'));

		I18n::$locale = 'de';

		$this->assertSame('Deutscher Test', I18n::translate('test'));
	}

	public function testUserMethod(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'userMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$user = new User([
			'email' => 'test@getkirby.com',
			'name'  => 'Test User'
		]);
		$this->assertSame('test', $user->test());

		// reset methods
		User::$methods = [];
	}

	public function testUserModel(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'userModels' => [
				'dummy' => DummyUser::class
			]
		]);

		$user = User::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(DummyUser::class, $user);
	}

	public function testUsersMethod(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'usersMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$users = new Users([]);
		$this->assertSame('test', $users->test());

		// reset methods
		Users::$methods = [];
	}

	public function testPluginLoader(): void
	{
		$phpUnit  = $this;
		$executed = 0;

		Dir::copy(static::FIXTURES . '/AppPluginsTest', $tmp = static::TMP);

		$kirby = new App([
			'roots' => [
				'index'   => static::TMP,
				'plugins' => static::TMP . '/site/plugins-loader'
			],
			'hooks' => [
				'system.loadPlugins:after' => function () use ($phpUnit, &$executed, $tmp) {
					$plugins = $this->plugins();

					if (count($plugins) === 2) {
						$phpUnit->assertSame([
							'kirby/manual1' => new Plugin('kirby/manual1', []),
							'kirby/manual2' => new Plugin('kirby/manual2', [])
						], $plugins);
					} else {
						$phpUnit->assertInstanceOf(Plugin::class, $plugins['kirby/test1']);
						$phpUnit->assertSame($tmp . '/site/plugins-loader/test1', $plugins['kirby/test1']->root());
					}

					$executed++;
				}
			]
		]);

		// the hook defined inside the test1 plugin should also have been called
		$this->assertTrue(static::$calledPluginsLoadedHook);

		// try loading again (which should *not* trigger the hooks again)
		$kirby->plugins();

		// overwrite plugins with a custom array
		$expected = [
			'kirby/manual1' => new Plugin('kirby/manual1', []),
			'kirby/manual2' => new Plugin('kirby/manual2', [])
		];
		$this->assertSame($expected, $kirby->plugins($expected));

		// hook should have been called only once after the first initialization
		$this->assertSame(1, $executed);
	}

	public function testPluginLoaderAnonymous(): void
	{
		Dir::copy(static::FIXTURES . '/AppPluginsTest', static::TMP);

		$kirby = new App([
			'roots' => [
				'index'   => static::TMP,
				'plugins' => $dir = static::TMP . '/site/plugins-loader-anonymous'
			]
		]);

		$plugins = $kirby->plugins();
		$this->assertCount(1, $plugins);

		$plugin = array_pop($plugins);
		$this->assertSame('plugins/test5', $plugin->name());
		$this->assertSame($dir . '/test5', $plugin->root());
	}

	public function testThirdPartyExtensions(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'tags' => [
				'test' => $testTag = function () {
				},
			],
			'thirdParty' => [
				'blocks' => [
					'test' => $testBlock = function () {
					}
				]
			]
		]);

		$this->assertSame($testTag, $kirby->extensions('tags')['test']);
		$this->assertSame($testBlock, $kirby->extensions('thirdParty')['blocks']['test']);
	}

	public function testNativeComponents(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'components' => [
				'url' => function ($kirby, $path) {
					return 'https://rewritten.getkirby.com/' . $path;
				},
			]
		]);

		$this->assertSame('https://rewritten.getkirby.com/test', $kirby->component('url')($kirby, 'test'));
		$this->assertSame('https://getkirby.com/test', $kirby->nativeComponent('url')($kirby, 'test'));
	}

	public function testAreas(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'areas' => [
				'todos' => fn () => []
			]
		]);

		$areas = $kirby->extensions('areas');

		$this->assertCount(1, $areas);
		$this->assertArrayHasKey('todos', $areas);
		$this->assertInstanceOf('Closure', $areas['todos'][0]);
	}

	public function testFileTypes(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'fileTypes' => [
				'm4p' => [
					'mime' => 'video/m4p',
					'type' => 'video',
				],
				'heif' => [
					'mime' => ['image/heic', 'image/heif'],
					'type' => 'image',
					'resizable' => true,
					'viewable' => true,
				],
				'test' => [
					'extension' => 'kql',
					'type' => 'code'
				],
				'midi' => [
					'mime' => 'audio/x-midi'
				],
				'ttf' => [
					'type' => 'font'
				]
			]
		]);

		$fileTypes = $kirby->extensions('fileTypes');
		$this->assertSame($fileTypes['type'], F::$types);
		$this->assertSame($fileTypes['mime'], Mime::$types);
		$this->assertSame($fileTypes['resizable'], Image::$resizableTypes);
		$this->assertSame($fileTypes['viewable'], Image::$viewableTypes);

		$this->assertContains('m4p', F::$types['video']);
		$this->assertArrayHasKey('m4p', Mime::$types);
		$this->assertSame('video/m4p', Mime::$types['m4p']);
		$this->assertNotContains('m4p', Image::$resizableTypes);
		$this->assertNotContains('m4p', Image::$viewableTypes);

		$this->assertContains('heif', F::$types['image']);
		$this->assertArrayHasKey('heif', Mime::$types);
		$this->assertSame(['image/heic', 'image/heif'], Mime::$types['heif']);
		$this->assertContains('heif', Image::$resizableTypes);
		$this->assertContains('heif', Image::$viewableTypes);

		$this->assertContains('kql', F::$types['code']);
		$this->assertNotContains('kql', Image::$resizableTypes);
		$this->assertNotContains('kql', Image::$viewableTypes);

		$this->assertArrayHasKey('midi', Mime::$types);
		$this->assertSame(['audio/midi', 'audio/x-midi'], Mime::$types['midi']);
		$this->assertNotContains('midi', Image::$resizableTypes);
		$this->assertNotContains('midi', Image::$viewableTypes);

		$this->assertArrayHasKey('font', F::$types);
		$this->assertContains('ttf', F::$types['font']);
		$this->assertNotContains('ttf', Image::$resizableTypes);
		$this->assertNotContains('ttf', Image::$viewableTypes);
	}
}
