<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Data\Data;
use Kirby\Exception\Exception;

/**
 * @coversDefaultClass \Kirby\Content\Translation
 * @covers ::__construct
 */
class TranslationTest extends TestCase
{
	/**
	 * @covers ::code
	 * @covers ::id
	 */
	public function testCodeAndId()
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

		$this->assertSame('en', $translationEN->language()->code());
		$this->assertSame('en', $translationEN->id());

		$this->assertSame('de', $translationDE->language()->code());
		$this->assertSame('de', $translationDE->id());
	}

	/**
	 * @covers ::content
	 */
	public function testContentMultiLanguage()
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

		$this->assertSame($expected['en']['content'], $translationEN->version()->content($languageEN)->toArray());
		$this->assertSame($expected['de']['content'], $translationDE->version()->content($languageDE)->toArray());
	}

	/**
	 * @covers ::content
	 */
	public function testContentSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::single()
		);

		$expected = $this->createContentSingleLanguage();

		$this->assertSame($expected['content'], $translation->version()->content()->toArray());
	}

	/**
	 * @covers ::contentFile
	 */
	public function testContentFileMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('en')
		);

		$this->assertSame($this->model->root() . '/article.en.txt', $translation->version()->contentFile());
	}

	/**
	 * @covers ::contentFile
	 */
	public function testContentFileSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::single()
		);

		$this->assertSame($this->model->root() . '/article.txt', $translation->version()->contentFile());
	}

	/**
	 * @covers ::create
	 */
	public function testCreate()
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
		$this->assertSame($content, $translation->version()->content()->toArray());
		$this->assertTrue($translation->version()->exists());
	}

	/**
	 * @covers ::create
	 */
	public function testCreateWithSlug()
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

		$this->assertSame(['title' => 'Test', 'slug' => 'foo'], $translation->version()->content()->toArray());
		$this->assertSame('foo', $translation->slug());
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsMultiLanguage()
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

		$this->assertFalse($translationEN->version()->exists());
		$this->assertFalse($translationDE->version()->exists());

		$this->createContentMultiLanguage();

		$this->assertTrue($translationEN->version()->exists());
		$this->assertTrue($translationDE->version()->exists());
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::single()
		);

		$this->assertFalse($translation->version()->exists());

		$this->createContentSingleLanguage();

		$this->assertTrue($translation->version()->exists());
	}

	/**
	 * @covers ::isDefault
	 */
	public function testIsDefault()
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

	/**
	 * @covers ::language
	 */
	public function testLanguage()
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

	/**
	 * @covers ::model
	 */
	public function testModel()
	{
		$this->setUpMultiLanguage();

		$translation = new Translation(
			model: $this->model,
			version: $this->model->version(),
			language: Language::ensure('en')
		);

		$this->assertSame($this->model, $translation->model());
	}

	/**
	 * @covers ::parent
	 */
	public function testParent()
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

	/**
	 * @covers ::slug
	 */
	public function testSlugExists()
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

	/**
	 * @covers ::slug
	 */
	public function testSlugNotExists()
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

	/**
	 * @covers ::update
	 */
	public function testUpdate()
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

	/**
	 * @covers ::toArray
	 */
	public function testToArray()
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

	/**
	 * @covers ::version
	 */
	public function testVersion()
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
