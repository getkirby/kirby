<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class LanguagesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Languages';

	protected $app;
	protected $languages;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_US',
					'url'     => '/',
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'locale'  => 'de_DE',
					'url'     => '/de',
				],
			]
		]);

		$this->languages = $this->app->languages();
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testCodes()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_US',
					'url'     => '/',
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'locale'  => 'de_DE',
					'url'     => '/de',
				],
			]
		]);

		$this->assertSame(['en', 'de'], $app->languages()->codes());

		$app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		$this->assertSame(['default'], $app->languages()->codes());
	}

	public function testEnsureInMultiLanguageMode()
	{
		$languages = Languages::ensure();

		$this->assertCount(2, $languages);
		$this->assertSame('en', $languages->first()->code());
		$this->assertSame('de', $languages->last()->code());
	}

	public function testEnsureInSingleLanguageMode()
	{
		new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		$languages = Languages::ensure();

		$this->assertCount(1, $languages);
		$this->assertSame('en', $languages->first()->code());
	}

	public function testLoad()
	{
		$this->assertCount(2, $this->languages);
		$this->assertSame(['en', 'de'], $this->languages->codes());
		$this->assertSame('en', $this->languages->default()->code());
	}

	public function testLoadFromFiles()
	{
		$this->app->clone([
			'roots' => [
				'languages' => static::TMP
			]
		]);

		Data::write(static::TMP . '/en.php', [
			'code' => 'en',
			'default' => true
		]);

		Data::write(static::TMP . '/de.php', [
			'code' => 'de'
		]);

		$languages = Languages::load();

		$this->assertCount(2, $languages);
		$this->assertSame(['de', 'en'], $languages->codes());
		$this->assertSame('en', $languages->default()->code());

		Dir::remove(static::TMP);
	}

	public function testDefault()
	{
		$this->assertSame('en', $this->languages->default()->code());
	}

	public function testMultipleDefault()
	{
		$this->expectException(DuplicateException::class);

		new App([
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_US',
					'url'     => '/',
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'default' => true,
					'locale'  => 'de_DE',
					'url'     => '/de',
				],
			]
		]);
	}

	public function testCreate()
	{
		$language = $this->app->languages()->create([
			'code' => 'tr'
		]);

		$this->assertSame('tr', $language->code());
		$this->assertFalse($language->isDefault());
		$this->assertSame('ltr', $language->direction());
		$this->assertSame('tr', $language->name());
		$this->assertSame('/tr', $language->url());
	}
}
