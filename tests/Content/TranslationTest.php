<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Translation::class)]
class TranslationTest extends TestCase
{
	public function testCodeAndId(): void
	{
		$this->setUpMultiLanguage();

		$translationEN = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('en')
		);

		$translationDE = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('de')
		);

		$this->assertSame('en', $translationEN->code());
		$this->assertSame('en', $translationEN->language()->code());
		$this->assertSame('en', $translationEN->id());

		$this->assertSame('de', $translationDE->code());
		$this->assertSame('de', $translationDE->language()->code());
		$this->assertSame('de', $translationDE->id());
	}

	public function testContentMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$translationEN = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: $languageEN = Language::ensure('en')
		);

		$translationDE = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: $languageDE = Language::ensure('de')
		);

		$expected = $this->createContentMultiLanguage();

		$this->assertSame($expected['en']['content'], $translationEN->content());
		$this->assertSame($expected['de']['content'], $translationDE->content());
	}

	public function testContentSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::single()
		);

		$expected = $this->createContentSingleLanguage();

		$this->assertSame($expected['content'], $translation->content());
	}

	public function testContentFileMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('en')
		);

		$this->assertSame($this->model->root() . '/article.en.txt', $translation->version()->contentFile());
	}

	public function testContentFileSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::single()
		);

		$this->assertSame($this->model->root() . '/article.txt', $translation->version()->contentFile());
	}

	public function testCreate(): void
	{
		$this->setUpMultiLanguage();

		$translation = Translation::create(
			model: $this->model,
			version: $version = $this->model->version(),
			language: $language = Language::ensure('en'),
			fields: $content = [
				'title' => 'Test'
			]
		);

		$this->assertSame($this->model, $translation->model());
		$this->assertSame($version, $translation->version());
		$this->assertSame($language, $translation->language());
		$this->assertSame($content, $translation->content());
		$this->assertTrue($translation->exists());
	}

	public function testCreateWithSlug(): void
	{
		$this->setUpMultiLanguage();

		$translation = Translation::create(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('en'),
			fields: [
				'title' => 'Test'
			],
			slug: 'foo'
		);

		$this->assertSame(['title' => 'Test', 'slug' => 'foo'], $translation->version()->read());
		$this->assertSame('foo', $translation->slug());
	}

	public function testExistsMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$translationEN = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: $languageEN = Language::ensure('en')
		);

		$translationDE = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: $languageDE = Language::ensure('de')
		);

		// the default version + default language exists as soon as the
		// model directory exists
		$this->assertTrue($translationEN->exists());

		// the secondary language only exists as soon as
		// the content file exists
		$this->assertFalse($translationDE->exists());

		$this->createContentMultiLanguage();

		$this->assertTrue($translationEN->exists());
		$this->assertTrue($translationDE->exists());
	}

	public function testExistsSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::single()
		);

		// the version exists as soon as the directory exists
		$this->assertTrue($translation->exists());
	}

	public function testIsDefault(): void
	{
		$this->setUpMultiLanguage();

		$en = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('en')
		);

		$de = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('de')
		);

		$this->assertTrue($en->language()->isDefault());
		$this->assertFalse($de->language()->isDefault());
	}

	public function testLanguage(): void
	{
		$this->setUpMultiLanguage();

		$translationEN = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: $languageEN = Language::ensure('en')
		);

		$translationDE = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: $languageDE = Language::ensure('de')
		);

		$this->assertSame($languageEN, $translationEN->language());
		$this->assertSame($languageDE, $translationDE->language());
	}

	public function testModel(): void
	{
		$this->setUpMultiLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('en')
		);

		$this->assertSame($this->model, $translation->model());
	}

	public function testParent(): void
	{
		$this->setUpSingleLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $version = $this->model->version(),
			language: Language::ensure('default')
		);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('`$translation->parent()` has been deprecated. Please use `$translation->model()` instead');

		$translation->parent();
	}

	public function testSlugExists(): void
	{
		$this->setUpMultiLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('de')
		);

		Data::write($this->contentFile('de'), [
			'title' => 'Test',
			'slug'  => 'german-slug'
		]);

		$this->assertSame('german-slug', $translation->slug());
	}

	public function testSlugNotExists(): void
	{
		$this->setUpMultiLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('de')
		);

		Data::write($this->contentFile('de'), [
			'title' => 'Test',
		]);

		$this->assertNull($translation->slug());
	}

	public function testUpdate(): void
	{
		$this->setUpSingleLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $version = $this->model->version(),
			language: Language::ensure('default')
		);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('`$translation->update()` has been deprecated. Please use `$model->version()->update()` instead');

		$translation->update();
	}

	public function testToArray(): void
	{
		$this->setUpSingleLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('en')
		);

		$expected = [
			'code'    => 'en',
			'content' => $this->createContentSingleLanguage()['content'],
			'exists'  => true,
			'slug'    => null,
		];

		$this->assertSame($expected, $translation->toArray());
	}

	public function testVersion(): void
	{
		$this->setUpMultiLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $version = $this->model->version(),
			language: Language::ensure('en')
		);

		$this->assertSame($version, $translation->version());
	}
}
