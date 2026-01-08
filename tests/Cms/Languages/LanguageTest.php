<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Language::class)]
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

	public function testConstructNoCode(): void
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

	#[DataProvider('baseUrlProvider')]
	public function testBaseUrl($kirbyUrl, $url, $expected): void
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

	public function testCreate(): void
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

	public function testCreateNoPermissions(): void
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

	public function testCreateWithoutLoggedUser(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create a language');

		Language::create([
			'code' => 'en'
		]);
	}

	public function testCreateHooks(): void
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

	public function testCodeAndId(): void
	{
		$language = new Language(['code' => 'en']);
		$this->assertSame('en', $language->code());
		$this->assertSame('en', $language->id());
	}

	public function testDelete(): void
	{
		$this->app->impersonate('kirby');

		$language = Language::create(['code' => 'en']);
		$this->assertTrue($language->delete());
	}

	public function testDeleteNoPermissions(): void
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

	public function testDeleteWithoutLoggedUser(): void
	{
		$this->app->impersonate('kirby');
		$language = Language::create(['code' => 'en']);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the language');

		// unimpersonate and test the method
		$this->app->impersonate();
		$language->delete();
	}

	public function testDeleteHooks(): void
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

	public function testDirection(): void
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

	public function testEnsureInMultiLanguageMode(): void
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

	public function testEnsureInSingleLanguageMode(): void
	{
		new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		$language = Language::ensure();

		$this->assertSame('en', $language->code());
	}

	public function testExists(): void
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

	public function testIs(): void
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

	public function testIsDefault(): void
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

	public function testIsSingle(): void
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

	public function testLocale(): void
	{
		$language = new Language([
			'code' => 'en',
			'locale' => 'en_US'
		]);

		$this->assertSame([LC_ALL => 'en_US'], $language->locale());
		$this->assertSame('en_US', $language->locale(LC_ALL));
	}

	public function testLocaleArray1(): void
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

	public function testLocaleArray2(): void
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

	public function testLocaleArray3(): void
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

	public function testLocaleInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$language = new Language([
			'code' => 'en',
			'locale' => 123
		]);
	}

	public function testLocaleDefault(): void
	{
		$language = new Language([
			'code' => 'en',
		]);

		$this->assertSame('en', $language->locale(LC_ALL));
	}

	public function testName(): void
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

	#[DataProvider('pathProvider')]
	public function testPath($input, $expected): void
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

	#[DataProvider('patternProvider')]
	public function testPattern($input, $expected): void
	{
		$language = new Language([
			'code' => 'en',
			'url'  => $input
		]);

		$this->assertSame($expected, $language->pattern());
	}

	public function testRoot(): void
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

	public function testRouter(): void
	{
		$language = new Language([
			'code' => 'de'
		]);

		$this->assertInstanceOf(LanguageRouter::class, $language->router());
	}

	public function testSave(): void
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

	public function testSingle(): void
	{
		$language = Language::single();

		$this->assertSame('en', $language->code());
		$this->assertSame('en', $language->name());
	}

	public function testToArrayAndDebuginfo(): void
	{
		$language = new Language([
			'code'   => 'de',
			'name'   => 'Deutsch',
			'locale' => 'de_DE',
		]);

		$expected = [
			'code'           => 'de',
			'default'        => false,
			'direction'      => 'ltr',
			'hasAbsoluteUrl' => false,
			'locale'         => [LC_ALL => 'de_DE'],
			'name'           => 'Deutsch',
			'rules'          => $language->rules(),
			'url'            => '/de'
		];

		$this->assertSame($expected, $language->toArray());
		$this->assertSame($expected, $language->__debugInfo());
	}

	public function testUrlWithRelativeValue(): void
	{
		$language = new Language([
			'code' => 'en',
			'url'  => 'super'
		]);

		$this->assertSame('/super', $language->url());
	}

	public function testUrlWithAbsoluteValue(): void
	{
		$language = new Language([
			'code' => 'en',
			'url'  => 'https://en.getkirby.com'
		]);

		$this->assertSame('https://en.getkirby.com', $language->url());
	}

	public function testUrlWithDash(): void
	{
		$language = new Language([
			'code' => 'en',
			'url'  => '/'
		]);

		$this->assertSame('/', $language->url());
	}

	public function testUrlDefault(): void
	{
		$language = new Language([
			'code' => 'en',
		]);

		$this->assertSame('/en', $language->url());
	}

	public function testHasAbsoluteUrl(): void
	{
		// default
		$language = new Language([
			'code' => 'en',
			'url'  => null
		]);
		$this->assertFalse($language->hasAbsoluteUrl());

		// relative url - false
		$language = new Language([
			'code' => 'en',
			'url'  => '/en'
		]);
		$this->assertFalse($language->hasAbsoluteUrl());

		// root url - false
		$language = new Language([
			'code' => 'en',
			'url'  => '/'
		]);
		$this->assertFalse($language->hasAbsoluteUrl());

		// absolute http url - true
		$language = new Language([
			'code' => 'en',
			'url'  => 'http://example.com'
		]);
		$this->assertTrue($language->hasAbsoluteUrl());

		// absolute https url - true
		$language = new Language([
			'code' => 'en',
			'url'  => 'https://example.com/en'
		]);
		$this->assertTrue($language->hasAbsoluteUrl());

		// no url - false
		$language = new Language([
			'code' => 'en'
		]);
		$this->assertFalse($language->hasAbsoluteUrl());
	}

	public function testUpdate(): void
	{
		Dir::make(static::TMP . '/content');

		$this->app->impersonate('kirby');

		$language = Language::create([
			'code' => 'en'
		]);

		$language = $language->update(['name' => 'English']);

		$this->assertSame('English', $language->name());
	}

	public function testUpdateDefault(): void
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

	public function testUpdateHooks(): void
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

	public function testUpdateNoPermissions(): void
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

	public function testUpdateWithoutLoggedUser(): void
	{
		$this->app->impersonate('kirby');
		$language = Language::create(['code' => 'en']);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the language');

		// unimpersonate and test the method
		$this->app->impersonate();
		$language->update(['name' => 'English']);
	}

	public function testVariable(): void
	{
		$language = new Language([
			'code' => 'en',
		]);

		$variable = $language->variable('test');
		$this->assertInstanceOf(LanguageVariable::class, $variable);
		$this->assertSame('test', $variable->key());
	}

	public function testVariableDecode(): void
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
