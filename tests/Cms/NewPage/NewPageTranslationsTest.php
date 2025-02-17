<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Content\MemoryStorage;
use Kirby\Data\Data;

/**
 * @coversDefaultClass \Kirby\Cms\NewPage
 */
class NewPageTranslationsTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageTranslationsTest';

	public function testSetContentInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
			'content' => $content = [
				'title'    => 'Title',
				'subtitle' => 'Subtitle'
			]
		]);

		$translation = $page->translation();

		$this->assertSame('en', $translation->code());
		$this->assertSame($content, $translation->content());
		$this->assertSame($content, $page->content()->toArray());
		$this->assertSame($content, $page->content('en')->toArray());
		$this->assertSame($content, $page->content('de')->toArray());

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.en.txt');
		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.de.txt');
	}

	public function testSetContentInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => $content = [
				'title'    => 'Title',
				'subtitle' => 'Subtitle'
			]
		]);

		$translation = $page->translation();

		$this->assertSame('en', $translation->code());
		$this->assertSame($content, $translation->content());
		$this->assertSame($content, $page->content()->toArray());

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.txt');
	}

	public function testSetTranslationsInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
			'translations' => [
				[
					'code'    => 'en',
					'content' => $contentEN = [
						'title'    => 'Title EN',
						'subtitle' => 'Subtitle EN'
					]
				],
				[
					'code'    => 'de',
					'content' => $contentDE = [
						'title'    => 'Title DE',
						'subtitle' => 'Subtitle DE'
					]
				]
			]
		]);

		$this->assertInstanceOf(MemoryStorage::class, $page->storage());

		$translationEN = $page->translation('en');
		$translationDE = $page->translation('de');

		$this->assertSame('en', $translationEN->code());
		$this->assertSame($contentEN, $translationEN->content());
		$this->assertSame($contentEN, $page->content('en')->toArray());

		$this->assertSame('de', $translationDE->code());
		$this->assertSame($contentDE, $translationDE->content());
		$this->assertSame($contentDE, $page->content('de')->toArray());

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.en.txt');
		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.de.txt');
	}

	public function testSetTranslationsInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug' => 'test',
			'translations' => [
				[
					'code'    => 'en',
					'content' => $content = [
						'title' => 'Title',
						'subtitle' => 'Subtitle'
					]
				]
			]
		]);

		$this->assertInstanceOf(MemoryStorage::class, $page->storage());

		$translation = $page->translation();

		$this->assertSame('en', $translation->code());
		$this->assertSame($content, $translation->content());
		$this->assertSame($content, $page->content()->toArray());

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.txt');
	}

	public function testTranslationsInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
		]);

		$translations = $page->translations();

		$this->assertCount(2, $translations);
		$this->assertSame('en', $translations->first()->code());
		$this->assertSame('de', $translations->last()->code());
	}

	public function testTranslationsOnDiskInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.en.txt');
		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.de.txt');

		Data::write(static::TMP . '/content/test/default.en.txt', $contentEN = [
			'title'    => 'Title EN',
			'subtitle' => 'Subtitle EN'
		]);

		Data::write(static::TMP . '/content/test/default.de.txt', $contentDE = [
			'title'    => 'Title DE',
			'subtitle' => 'Subtitle DE'
		]);

		$translations = $page->translations();

		$this->assertSame($contentEN, $translations->find('en')->content());
		$this->assertSame($contentDE, $translations->find('de')->content());
	}

	public function testTranslationsInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$translations = $page->translations();

		$this->assertCount(1, $translations);
		$this->assertSame('en', $translations->first()->code());
	}

	public function testTranslationsOnDiskInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.txt');

		Data::write(static::TMP . '/content/test/default.txt', $content = [
			'title'    => 'Title',
			'subtitle' => 'Subtitle'
		]);

		$translations = $page->translations();

		$this->assertSame($content, $translations->find('en')->content());
	}
}
