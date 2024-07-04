<?php

namespace Kirby\Content;

use Kirby\Exception\NotFoundException;

/**
 * @coversDefaultClass \Kirby\Content\Translations
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
		$this->assertSame('en', $translations->first()->language()->code());
		$this->assertSame('de', $translations->last()->language()->code());
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
		$this->assertSame('en', $translations->first()->language()->code());
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

		$this->assertSame('en', $translations->findByKey('en')->language()->code());
		$this->assertSame('en', $translations->findByKey('default')->language()->code());
		$this->assertSame('en', $translations->findByKey('current')->language()->code());
		$this->assertSame('de', $translations->findByKey('de')->language()->code());
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

		$this->assertSame('en', $translations->findByKey('en')->language()->code());
		$this->assertSame('en', $translations->findByKey('default')->language()->code());
		$this->assertSame('en', $translations->findByKey('current')->language()->code());
	}

	/**
	 * @covers ::findByKey
	 */
	public function testFindByKeyWithInvalidLanguage()
	{
		$this->setUpMultiLanguage();

		$translations = Translations::load(
			model: $this->model,
			version: $this->model->version()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Invalid language: fr');

		$translations->findByKey('fr');
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
		$this->assertSame('en', $translations->first()->language()->code());
		$this->assertSame('de', $translations->last()->language()->code());
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
		$this->assertSame('en', $translations->first()->language()->code());
		$this->assertTrue($translations->first()->language()->isSingle());
	}
}
