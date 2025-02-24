<?php

namespace Kirby\Cms;

use Kirby\Content\MemoryStorage;
use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageTranslationsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageTranslations';

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

	public function testUntranslatedFields(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug'         => 'test',
			'translations' => [
				[
					'code'    => 'en',
					'content' => [
						'title' => 'Title EN'
					]
				],
				[
					'code'    => 'de',
					'content' => []
				]
			]
		]);

		$this->assertSame('Title EN', $page->title()->value());
		$this->assertSame('Title EN', $page->title('en')->value());
		$this->assertSame('Title EN', $page->title('de')->value());
	}

	public function testUntranslatableFields(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'fields' => [
					'a' => [
						'type' => 'text'
					],
					'b' => [
						'type' => 'text',
						'translate' => false
					],
				]
			],
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'a' => 'A (EN)',
						'b' => 'B (EN)'
					]
				],
				[
					'code' => 'de',
					'content' => [
						'a' => 'A (DE)',
						'b' => 'B (DE)'
					]
				]
			]
		]);

		$contentEN = $page->content('en');
		$contentDE = $page->content('de');

		$this->assertSame('A (EN)', $contentEN->a()->value());
		$this->assertSame('B (EN)', $contentEN->b()->value());

		$this->assertSame('A (DE)', $contentDE->a()->value());
		$this->assertSame('B (EN)', $contentDE->b()->value(), 'The untranslated field should have the value of the default language');
	}
}
