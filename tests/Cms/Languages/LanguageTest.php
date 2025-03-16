<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\Language
 */
class LanguageTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Language';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructNoCode()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The property "code" is required');

		new Language([]);
	}

	public static function baseUrlProvider(): array
	{
		return [
			['https://getkirby.com', null, 'https://getkirby.com'],
			['https://getkirby.com', '/en', 'https://getkirby.com'],
			['https://getkirby.com', 'https://getkirby.de', 'https://getkirby.de'],
			['https://getkirby.com', 'https://getkirby.de/en', 'https://getkirby.de'],
			['http://localhost/example.com', null, 'http://localhost/example.com'],
			['http://localhost/example.com', '/en', 'http://localhost/example.com'],
			['http://localhost/example.com', 'http://getkirby.com', 'http://getkirby.com'],
			['http://localhost/example.com', 'http://getkirby.com/en', 'http://getkirby.com'],
		];
	}

	/**
	 * @covers ::baseUrl
	 * @dataProvider baseUrlProvider
	 */
	public function testBaseUrl($kirbyUrl, $url, $expected)
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => $kirbyUrl
			]
		]);

		// default
		$language = new Language([
			'code' => 'en',
			'url'  => $url
		]);

		$this->assertSame($expected, $language->baseUrl());
	}

	/**
	 * @covers ::create
	 */
	public function testCreate()
	{
		$this->app->impersonate('kirby');

		$language = Language::create([
			'code' => 'en'
		]);

		$this->assertSame('en', $language->code());
		$this->assertTrue($language->isDefault());
		$this->assertSame('ltr', $language->direction());
		$this->assertSame('en', $language->name());
		$this->assertSame('/en', $language->url());
	}

	/**
	 * @covers ::create
	 */
	public function testCreateNoPermissions()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'create' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create a language');

		$app->impersonate('test@getkirby.com');
		Language::create([
			'code' => 'en'
		]);
	}

	/**
	 * @covers ::create
	 */
	public function testCreateWithoutLoggedUser()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create a language');

		Language::create([
			'code' => 'en'
		]);
	}

	/**
	 * @covers ::create
	 */
	public function testCreateHooks()
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'language.create:before' => function (Language $language, array $input) use ($phpunit, &$calls) {
					$phpunit->assertInstanceOf(Language::class, $language);
					$phpunit->assertSame('de', $input['code']);
					$phpunit->assertTrue($input['default']);
					$calls++;
				},
				'language.create:after' => function (Language $language, array $input) use ($phpunit, &$calls) {
					$phpunit->assertInstanceOf(Language::class, $language);
					$phpunit->assertSame('de', $input['code']);
					$phpunit->assertTrue($input['default']);
					$calls++;
				}
			]
		]);

		$app->impersonate('kirby');

		Language::create([
			'code' => 'de'
		]);

		$this->assertSame(2, $calls);
	}

	/**
	 * @covers ::code
	 * @covers ::id
	 */
	public function testCodeAndId()
	{
		$language = new Language(['code' => 'en']);
		$this->assertSame('en', $language->code());
		$this->assertSame('en', $language->id());
	}

	/**
	 * @covers ::delete
	 */
	public function testDelete()
	{
		$this->app->impersonate('kirby');

		$language = Language::create(['code' => 'en']);
		$this->assertTrue($language->delete());
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNoPermissions()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'create' => true,
							'delete' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the language');

		$app->impersonate('test@getkirby.com');
		$language = Language::create(['code' => 'en']);
		$language->delete();
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteWithoutLoggedUser()
	{
		$this->app->impersonate('kirby');
		$language = Language::create(['code' => 'en']);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the language');

		// unimpersonate and test the method
		$this->app->impersonate();
		$language->delete();
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteHooks()
	{
		$calls = 0;
		$phpunit = $this;

		$app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'hooks' => [
				'language.delete:before' => function (Language $language) use ($phpunit, &$calls) {
					$phpunit->assertInstanceOf(Language::class, $language);
					$phpunit->assertSame('en', $language->code());
					$phpunit->assertSame('English', $language->name());
					$calls++;
				},
				'language.delete:after' => function (Language $language) use ($phpunit, &$calls) {
					$phpunit->assertInstanceOf(Language::class, $language);
					$phpunit->assertSame('en', $language->code());
					$phpunit->assertSame('English', $language->name());
					$calls++;
				}
			]
		]);

		$app->impersonate('kirby');

		$language = Language::create([
			'code' => 'en',
			'name' => 'English'
		]);
		$language->delete();


		$this->assertSame(2, $calls);
	}

	/**
	 * @covers ::direction
	 */
	public function testDirection()
	{
		//default
		$language = new Language([
			'code'      => 'en'
		]);
		$this->assertSame('ltr', $language->direction());

		// rtl
		$language = new Language([
			'code'      => 'en',
			'direction' => 'rtl'
		]);
		$this->assertSame('rtl', $language->direction());

		// ltr
		$language = new Language([
			'code'      => 'en',
			'direction' => 'ltr'
		]);
		$this->assertSame('ltr', $language->direction());

		// invalid value
		$language = new Language([
			'code'      => 'en',
			'direction' => 'invalid'
		]);
		$this->assertSame('ltr', $language->direction());
	}

	public function testEnsureInMultiLanguageMode()
	{
		$app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				],
				[
					'code'    => 'de',
				],
			]
		]);

		// default language
		$language = Language::ensure();

		$this->assertSame('en', $language->code());

		// with language code
		$language = Language::ensure('de');

		$this->assertSame('de', $language->code());

		// with language object
		$language = Language::ensure($app->language('de'));

		$this->assertSame('de', $language->code());

		// with `current` keyword
		$language = Language::ensure('current');

		$this->assertSame('en', $language->code());

		// with `default` keyword
		$language = Language::ensure('default');

		$this->assertSame('en', $language->code());
	}

	public function testEnsureInSingleLanguageMode()
	{
		new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		$language = Language::ensure();

		$this->assertSame('en', $language->code());
	}

	/**
	 * @covers ::exists
	 */
	public function testExists()
	{
		new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$language = new Language([
			'code' => 'de'
		]);

		$this->assertFalse($language->exists());

		F::write($language->root(), 'test');

		$this->assertTrue($language->exists());
	}

	/**
	 * @covers ::is
	 */
	public function testIs()
	{
		$app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				],
				[
					'code'    => 'de',
				],
			]
		]);

		$en = new Language([
			'code' => 'en'
		]);

		$de = new Language([
			'code' => 'de'
		]);

		$this->assertTrue($en->is('en'));
		$this->assertTrue($en->is($en));
		$this->assertTrue($en->is(new Language(['code' => 'en'])));

		$this->assertFalse($en->is('de'));
		$this->assertFalse($en->is($de));
	}

	/**
	 * @covers ::isAccessible
	 */
	public function testIsAccessible()
	{
		$app = new App([
			'languages' => [
				[
					'code' => 'en'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'access' => false
						],
					]
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			],
		]);

		$language = $app->language('en');

		$app->impersonate('editor@getkirby.com');
		$this->assertFalse($language->isAccessible());

		$app->impersonate('kirby');
		$this->assertTrue($language->isAccessible());
	}

	/**
	 * @covers ::isDefault
	 */
	public function testIsDefault()
	{
		// default
		$language = new Language([
			'code' => 'en'
		]);
		$this->assertFalse($language->isDefault());

		// true
		$language = new Language([
			'code'    => 'en',
			'default' => true
		]);
		$this->assertTrue($language->isDefault());

		// false
		$language = new Language([
			'code'    => 'en',
			'default' => false
		]);
		$this->assertFalse($language->isDefault());

		// invalid
		$language = new Language([
			'code'    => 'en',
			'default' => 'foo'
		]);
		$this->assertFalse($language->isDefault());
	}

	/**
	 * @covers ::isListable
	 */
	public function testIsListable()
	{
		$app = new App([
			'languages' => [
				[
					'code' => 'en'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor-access',
					'permissions' => [
						'languages' => [
							'access' => false
						],
					]
				],
				[
					'name' => 'editor-list',
					'permissions' => [
						'languages' => [
							'list' => false
						],
					]
				]
			],
			'users' => [
				[
					'email' => 'editor-access@getkirby.com',
					'role'  => 'editor-access'
				],
				[
					'email' => 'editor-list@getkirby.com',
					'role'  => 'editor-list'
				]
			],
		]);

		$language = $app->language('en');

		$app->impersonate('editor-access@getkirby.com');
		$this->assertFalse($language->isListable());

		$app->impersonate('editor-list@getkirby.com');
		$this->assertFalse($language->isListable());

		$app->impersonate('kirby');
		$this->assertTrue($language->isListable());
	}

	/**
	 * @covers ::isSingle
	 */
	public function testIsSingle()
	{
		// default
		$language = new Language([
			'code' => 'en'
		]);

		$this->assertFalse($language->isSingle());

		// true
		$language = new Language([
			'code'   => 'en',
			'single' => true
		]);

		$this->assertTrue($language->isSingle());
	}

	/**
	 * @covers ::locale
	 */
	public function testLocale()
	{
		$language = new Language([
			'code' => 'en',
			'locale' => 'en_US'
		]);

		$this->assertSame([LC_ALL => 'en_US'], $language->locale());
		$this->assertSame('en_US', $language->locale(LC_ALL));
	}

	/**
	 * @covers ::locale
	 */
	public function testLocaleArray1()
	{
		$language = new Language([
			'code' => 'en',
			'locale' => [
				LC_ALL   => 'en_US',
				LC_CTYPE => 'en_US.utf8'
			]
		]);

		$this->assertSame([
			LC_ALL   => 'en_US',
			LC_CTYPE => 'en_US.utf8'
		], $language->locale());
		$this->assertSame('en_US', $language->locale(LC_ALL));
		$this->assertSame('en_US.utf8', $language->locale(LC_CTYPE));
		$this->assertSame('en_US', $language->locale(LC_MONETARY));
	}

	/**
	 * @covers ::locale
	 */
	public function testLocaleArray2()
	{
		$language = new Language([
			'code' => 'en',
			'locale' => [
				LC_CTYPE => 'en_US.utf8'
			]
		]);

		$this->assertSame([
			LC_CTYPE => 'en_US.utf8'
		], $language->locale());
		$this->assertNull($language->locale(LC_ALL));
		$this->assertSame('en_US.utf8', $language->locale(LC_CTYPE));
		$this->assertNull($language->locale(LC_MONETARY));
	}

	/**
	 * @covers ::locale
	 */
	public function testLocaleArray3()
	{
		$language = new Language([
			'code' => 'en',
			'locale' => [
				'LC_ALL'   => 'en_US',
				'LC_CTYPE' => 'en_US.utf8'
			]
		]);

		$this->assertSame([
			LC_ALL   => 'en_US',
			LC_CTYPE => 'en_US.utf8'
		], $language->locale());
		$this->assertSame('en_US', $language->locale(LC_ALL));
		$this->assertSame('en_US.utf8', $language->locale(LC_CTYPE));
		$this->assertSame('en_US', $language->locale(LC_MONETARY));
	}

	/**
	 * @covers ::locale
	 */
	public function testLocaleInvalid()
	{
		$this->expectException(InvalidArgumentException::class);

		$language = new Language([
			'code' => 'en',
			'locale' => 123
		]);
	}

	/**
	 * @covers ::locale
	 */
	public function testLocaleDefault()
	{
		$language = new Language([
			'code' => 'en',
		]);

		$this->assertSame('en', $language->locale(LC_ALL));
	}

	/**
	 * @covers ::name
	 */
	public function testName()
	{
		$language = new Language([
			'code' => 'en',
			'name' => 'English'
		]);

		$this->assertSame('English', $language->name());

		// default
		$language = new Language([
			'code' => 'en',
		]);

		$this->assertSame('en', $language->name());
	}

	public static function pathProvider(): array
	{
		return [
			[null, 'en'],
			['/', ''],
			['/en', 'en'],
			['/en/', 'en'],
			['https://getkirby.com/en', 'en'],
			['https://getkirby.com/en/', 'en'],
			['https://getkirby.com/sub/sub', 'sub/sub'],
			['https://getkirby.com/sub/sub/', 'sub/sub'],
		];
	}

	/**
	 * @covers ::path
	 * @dataProvider pathProvider
	 */
	public function testPath($input, $expected)
	{
		$language = new Language([
			'code' => 'en',
			'url'  => $input
		]);

		$this->assertSame($expected, $language->path());
	}

	public static function patternProvider(): array
	{
		return [
			[null, 'en/(:all?)'],
			['/', '(:all)'],
			['/en', 'en/(:all?)'],
			['/en/', 'en/(:all?)'],
			['https://getkirby.com', '(:all)'],
			['https://getkirby.com/', '(:all)'],
			['https://getkirby.com/en', 'en/(:all?)'],
			['https://getkirby.com/en/', 'en/(:all?)'],
			['https://getkirby.com/sub/sub', 'sub/sub/(:all?)'],
			['https://getkirby.com/sub/sub/', 'sub/sub/(:all?)'],
		];
	}

	/**
	 * @covers ::pattern
	 * @dataProvider patternProvider
	 */
	public function testPattern($input, $expected)
	{
		$language = new Language([
			'code' => 'en',
			'url'  => $input
		]);

		$this->assertSame($expected, $language->pattern());
	}

	/**
	 * @covers ::root
	 */
	public function testRoot()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$language = new Language([
			'code' => 'de'
		]);

		$this->assertSame(static::TMP . '/site/languages/de.php', $language->root());
	}

	/**
	 * @covers ::router
	 */
	public function testRouter()
	{
		$language = new Language([
			'code' => 'de'
		]);

		$this->assertInstanceOf(LanguageRouter::class, $language->router());
	}

	/**
	 * @covers ::save
	 */
	public function testSave()
	{
		$app = new App([
			'roots' => [
				'index'     => static::TMP,
				'languages' => static::TMP
			]
		]);

		$file = static::TMP . '/de.php';

		// default
		$language = new Language([
			'code' => 'de',
		]);

		$language->save();

		$data = include $file;

		$this->assertSame('de', $data['code']);
		$this->assertFalse($data['default']);
		$this->assertSame('ltr', $data['direction']);
		$this->assertSame(['LC_ALL' => 'de'], $data['locale']);
		$this->assertSame('de', $data['name']);
		$this->assertSame([], $data['translations']);
		$this->assertNull($data['url'] ?? null);


		// custom url
		$language = new Language([
			'code' => 'de',
			'url'  => '/'
		]);

		$language->save();

		$data = include $file;

		$this->assertSame('/', $data['url']);


		// custom translations
		$language = new Language([
			'code' => 'de',
			'translations'  => [
				'foo' => 'bar'
			]
		]);

		$language->save();

		$data = include $file;

		$this->assertSame(['foo' => 'bar'], $data['translations']);


		// custom props in file
		Data::write($file, ['custom' => 'test']);

		$language = new Language([
			'code' => 'de'
		]);

		$language->save();

		$data = include $file;

		$this->assertSame('test', $data['custom']);
	}

	/**
	 * @covers ::single
	 */
	public function testSingle()
	{
		$language = Language::single();

		$this->assertSame('en', $language->code());
		$this->assertSame('en', $language->name());
	}

	/**
	 * @covers ::toArray
	 * @covers ::__debugInfo
	 */
	public function testToArrayAndDebuginfo()
	{
		$language = new Language([
			'code'   => 'de',
			'name'   => 'Deutsch',
			'locale' => 'de_DE',
		]);

		$expected = [
			'code'      => 'de',
			'default'   => false,
			'direction' => 'ltr',
			'locale'    => [LC_ALL => 'de_DE'],
			'name'      => 'Deutsch',
			'rules'     => $language->rules(),
			'url'       => '/de'
		];

		$this->assertSame($expected, $language->toArray());
		$this->assertSame($expected, $language->__debugInfo());
	}

	/**
	 * @covers ::url
	 */
	public function testUrlWithRelativeValue()
	{
		$language = new Language([
			'code' => 'en',
			'url'  => 'super'
		]);

		$this->assertSame('/super', $language->url());
	}

	/**
	 * @covers ::url
	 */
	public function testUrlWithAbsoluteValue()
	{
		$language = new Language([
			'code' => 'en',
			'url'  => 'https://en.getkirby.com'
		]);

		$this->assertSame('https://en.getkirby.com', $language->url());
	}

	/**
	 * @covers ::url
	 */
	public function testUrlWithDash()
	{
		$language = new Language([
			'code' => 'en',
			'url'  => '/'
		]);

		$this->assertSame('/', $language->url());
	}

	/**
	 * @covers ::url
	 */
	public function testUrlDefault()
	{
		$language = new Language([
			'code' => 'en',
		]);

		$this->assertSame('/en', $language->url());
	}

	/**
	 * @covers ::update
	 */
	public function testUpdate()
	{
		Dir::make(static::TMP . '/content');

		$this->app->impersonate('kirby');

		$language = Language::create([
			'code' => 'en'
		]);

		$language = $language->update(['name' => 'English']);

		$this->assertSame('English', $language->name());
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateDefault()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		$app->impersonate('kirby');

		$this->assertFalse($app->multilang());
		$this->assertNull($app->defaultLanguage());
		$this->assertSame(0, $app->languages()->count());

		$app->languages()->create([
			'code' => 'en',
			'default' => true
		]);

		$this->assertTrue($app->multilang());
		$this->assertSame('en', $app->defaultLanguage()->code());
		$this->assertSame(1, $app->languages()->count());
		$this->assertTrue($app->language('en')->isDefault());

		$de = $app->languages()->create([
			'code' => 'de'
		]);

		$this->assertTrue($app->multilang());
		$this->assertSame('en', $app->defaultLanguage()->code());
		$this->assertSame(2, $app->languages()->count());
		$this->assertSame($de, $app->language('de'));
		$this->assertTrue($app->language('en')->isDefault());
		$this->assertFalse($app->language('de')->isDefault());

		$app->language('de')->update(['default' => true]);

		$this->assertSame('de', $app->defaultLanguage()->code());
		$this->assertSame(2, $app->languages()->count());
		$this->assertTrue($app->language('de')->isDefault());
		$this->assertFalse($app->language('en')->isDefault());

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Please select another language to be the primary language');

		$app->language('de')->update(['default' => false]);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateHooks()
	{
		$calls = 0;
		$phpunit = $this;

		Dir::make(static::TMP . '/content');

		$app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'hooks' => [
				'language.update:before' => function (Language $language, array $input) use ($phpunit, &$calls) {
					$phpunit->assertInstanceOf(Language::class, $language);
					$phpunit->assertSame('en', $language->code());
					$phpunit->assertTrue($language->isDefault());
					$phpunit->assertSame('English', $input['name']);
					$phpunit->assertSame('en', $language->name());
					$calls++;
				},
				'language.update:after' => function (Language $oldLanguage, Language $newLanguage, array $input) use ($phpunit, &$calls) {
					$phpunit->assertInstanceOf(Language::class, $oldLanguage);
					$phpunit->assertInstanceOf(Language::class, $newLanguage);
					$phpunit->assertSame('en', $oldLanguage->code());
					$phpunit->assertSame('en', $newLanguage->code());
					$phpunit->assertTrue($oldLanguage->isDefault());
					$phpunit->assertTrue($newLanguage->isDefault());
					$phpunit->assertSame('English', $input['name']);
					$phpunit->assertSame('en', $oldLanguage->name());
					$phpunit->assertSame('English', $newLanguage->name());
					$calls++;
				}
			]
		]);

		$app->impersonate('kirby');

		$language = Language::create(['code' => 'en']);
		$language->update(['name' => 'English']);

		$this->assertSame(2, $calls);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateNoPermissions()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name'        => 'editor',
					'permissions' => [
						'languages' => [
							'create' => true,
							'update' => false
						]
					]
				],
			],
			'users'      => [
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the language');

		$app->impersonate('test@getkirby.com');

		$language = Language::create(['code' => 'en']);
		$language->update(['name' => 'English']);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateWithoutLoggedUser()
	{
		$this->app->impersonate('kirby');
		$language = Language::create(['code' => 'en']);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the language');

		// unimpersonate and test the method
		$this->app->impersonate();
		$language->update(['name' => 'English']);
	}

	/**
	 * @covers ::variable
	 */
	public function testVariable()
	{
		$language = new Language([
			'code' => 'en',
		]);

		$variable = $language->variable('test');
		$this->assertInstanceOf(LanguageVariable::class, $variable);
		$this->assertSame('test', $variable->key());
	}

	/**
	 * @covers ::variable
	 */
	public function testVariableDecode()
	{
		$language = new Language([
			'code' => 'en',
		]);

		$key = 'key with space';
		$encode = base64_encode(rawurlencode($key));
		$variable = $language->variable($encode, true);

		$this->assertInstanceOf(LanguageVariable::class, $variable);
		$this->assertSame($key, $variable->key());
	}
}
