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
	}

	public function testFindByKeyMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

	}

	public function testLoadMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

	}
}
