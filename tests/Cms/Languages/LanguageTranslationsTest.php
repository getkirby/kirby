<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\LanguageTranslations
 */
class LanguageTranslationsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP = KIRBY_TMP_DIR . '/Cms.LanguageTranslations';

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::root
	 */
	public function testRoot()
	{
		new App([
			'roots' => [
				'translations' => static::FIXTURES
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'en']));
		$this->assertSame(static::FIXTURES . '/en.php', $translations->root());
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray()
	{
		new App([
			'roots' => [
				'translations' => static::FIXTURES
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'en']));
		$this->assertSame([
			'hello' => 'Hello world!',
			'login' => 'Log-in',
		], $translations->toArray());
	}

	/**
	 * @covers ::get
	 */
	public function testGet()
	{
		new App([
			'roots' => [
				'translations' => static::FIXTURES
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'de']));
		$this->assertSame('Hallo Welt!', $translations->get('hello'));
	}

	/**
	 * @covers ::get
	 */
	public function testGetDefault()
	{
		new App([
			'roots' => [
				'translations' => static::FIXTURES
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'en']));
		$this->assertSame('Hello dear!', $translations->get('not-exists', 'Hello dear!'));
	}

	/**
	 * @covers ::setTranslations
	 * @covers ::get
	 */
	public function testSetTranslations()
	{
		new App([
			'roots' => [
				'translations' => static::FIXTURES
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'en']));
		$translations->setTranslations(['click' => 'Click here!']);
		$this->assertSame('Click here!', $translations->get('click'));
	}

	/**
	 * @covers ::set
	 * @covers ::get
	 */
	public function testSet()
	{
		new App([
			'roots' => [
				'translations' => static::FIXTURES
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'en']));
		$translations->set('contact', 'Contact us');
		$this->assertSame('Contact us', $translations->get('contact'));
	}

	/**
	 * @covers ::save
	 */
	public function testSave()
	{
		new App([
			'roots' => [
				'translations' => static::TMP,
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'tr']));
		$variables = ['write' => 'Bize yazın'];
		$translations->save($variables);

		$this->assertSame($variables, $translations->toArray());
	}

	/**
	 * @covers ::remove
	 */
	public function testRemove()
	{
		new App([
			'roots' => [
				'translations' => static::FIXTURES
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'en']));
		$translations->remove('login');
		$this->assertSame(['hello' => 'Hello world!'], $translations->toArray());
	}

	/**
	 * @covers ::load
	 */
	public function testLoad()
	{
		new App([
			'roots' => [
				'translations' => static::FIXTURES
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'en']));
		$this->assertSame([
			'hello' => 'Hello world!',
			'login' => 'Log-in',
		], $translations->load(['other' => 'Other variable']));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadDefault()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$translations = new LanguageTranslations(new Language(['code' => 'en']));
		$variables = ['hello' => 'Hello Kirby lovers!'];
		$this->assertSame($variables, $translations->load($variables));
	}
}
