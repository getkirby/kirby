<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

class LanguagesTest extends TestCase
{
	protected $app;
	protected $languages;
	protected $tmp;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp = __DIR__ . '/tmp/LanguagesTest',
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
		Dir::remove($this->tmp);
	}

	public function testCodes()
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp = __DIR__ . '/tmp/LanguagesTest',
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
				'index' => $this->tmp = __DIR__ . '/tmp/LanguagesTest',
			]
		]);

		$this->assertSame(['default'], $app->languages()->codes());
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
				'languages' => $root = __DIR__ . '/tmp/LanguagesTest'
			]
		]);

		Data::write($root . '/en.php', [
			'code' => 'en',
			'default' => true
		]);

		Data::write($root . '/de.php', [
			'code' => 'de'
		]);

		$languages = Languages::load();

		$this->assertCount(2, $languages);
		$this->assertSame(['de', 'en'], $languages->codes());
		$this->assertSame('en', $languages->default()->code());

		Dir::remove($root);
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
