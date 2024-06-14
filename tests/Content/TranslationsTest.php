<?php

namespace Kirby\Content;

/**
 * @coversDefaultClass Kirby\Content\Translations
 * @covers ::__construct
 */
class TranslationsTest extends TestCase
{
	/**
	 * @covers ::create
	 */
	public function testCreateMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$translations = Translations::create(
			model: $this->model,
			version: $this->model->version(),
			translations: [
				[
					'code' 	  => 'en',
					'content' => [
						'title' => 'Title English'
					]
				],
				[
					'code' 	  => 'de',
					'content' => [
						'title' => 'Title Deutsch'
					]
				]
			]
		);

		$this->assertCount(2, $translations);
		$this->assertSame('en', $translations->first()->code());
		$this->assertSame('de', $translations->last()->code());
	}

	/**
	 * @covers ::create
	 */
	public function testCreateSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$translations = Translations::create(
			model: $this->model,
			version: $this->model->version(),
			translations: [
				[
					'code' 	  => 'en',
					'content' => [
						'title' => 'Title English'
					]
				],
				// should be ignored because the matching language is not installed
				[
					'code' 	  => 'de',
					'content' => [
						'title' => 'Title Deutsch'
					]
				]
			]
		);

		$this->assertCount(1, $translations);
		$this->assertSame('en', $translations->first()->code());
		$this->assertTrue($translations->first()->language()->isSingle());
	}

	/**
	 * @covers ::findByKey
	 */
	public function testFindByKeyMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$translations = Translations::load(
			model: $this->model,
			version: $this->model->version()
		);

		$this->assertSame('en', $translations->findByKey('en')->code());
		$this->assertSame('en', $translations->findByKey('default')->code());
		$this->assertSame('en', $translations->findByKey('current')->code());
		$this->assertSame('de', $translations->findByKey('de')->code());
		$this->assertNull($translations->findByKey('fr'));
	}

	/**
	 * @covers ::findByKey
	 */
	public function testFindByKeySingleLanguage()
	{
		$this->setUpSingleLanguage();

		$translations = Translations::load(
			model: $this->model,
			version: $this->model->version()
		);

		$this->assertSame('en', $translations->findByKey('en')->code());
		$this->assertSame('en', $translations->findByKey('default')->code());
		$this->assertSame('en', $translations->findByKey('current')->code());
	}

	/**
	 * @covers ::load
	 */
	public function testLoadMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$translations = Translations::load(
			model: $this->model,
			version: $this->model->version()
		);

		$this->assertCount(2, $translations);
		$this->assertSame('en', $translations->first()->code());
		$this->assertSame('de', $translations->last()->code());
	}

	/**
	 * @covers ::load
	 */
	public function testLoadSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$translations = Translations::load(
			model: $this->model,
			version: $this->model->version()
		);

		$this->assertCount(1, $translations);
		$this->assertSame('en', $translations->first()->code());
		$this->assertTrue($translations->first()->language()->isSingle());
	}
}
