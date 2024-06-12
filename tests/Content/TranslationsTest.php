<?php

namespace Kirby\Content;

/**
 * @coversDefaultClass Kirby\Content\Translations
 * @covers ::__construct
 */
class TranslationsTest extends TestCase
{
	public function testCreateMultiLanguage(): void
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

	public function testFindByKeyMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$translations = Translations::load(
			model: $this->model,
			version: $this->model->version()
		);

		$this->assertSame('en', $translations->findByKey('en')->code());
		$this->assertSame('de', $translations->findByKey('de')->code());
		$this->assertNull($translations->findByKey('fr'));
	}

	public function testLoadMultiLanguage(): void
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
}
