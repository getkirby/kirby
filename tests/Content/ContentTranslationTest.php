<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Content\ContentTranslation
 */
class ContentTranslationTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::code
	 * @covers ::id
	 * @covers ::parent
	 */
	public function testParentAndCode()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$translation = new ContentTranslation([
			'parent' => $page,
			'code'   => 'de'
		]);

		$this->assertIsPage($page, $translation->parent());
		$this->assertSame('de', $translation->code());
		$this->assertSame('de', $translation->id());
	}

	/**
	 * @covers ::__construct
	 * @covers ::content
	 * @covers ::slug
	 */
	public function testContentAndSlug()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$translation = new ContentTranslation([
			'parent'  => $page,
			'code'    => 'de',
			'slug'    => 'test',
			'content' => $content = [
				'title' => 'test'
			]
		]);

		$this->assertSame('test', $translation->slug());
		$this->assertSame($content, $translation->content());
	}

	/**
	 * @covers ::content
	 */
	public function testContentMerged()
	{
		$app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			]
		]);

		$page = new Page([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'en',
					'content' => $defaultContent = [
						'title' => 'test',
						'date'  => '2042-01-01'
					]
				]
			]
		]);

		$translation = new ContentTranslation([
			'parent'  => $page,
			'code'    => 'de',
			'content' => $content = [
				'title' => 'translated test',
				'text'  => 'lorem test'
			]
		]);

		$this->assertSame([
			'title' => $content['title'],
			'date'  => $defaultContent['date'],
			'text'  => $content['text']
		], $translation->content());
	}

	/**
	 * @covers ::contentFile
	 */
	public function testContentFile()
	{
		$app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			],
			'roots' => [
				'content' => '/content',
			],
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'project'
		]);

		$translation = new ContentTranslation([
			'parent' => $page,
			'code'   => 'de',
		]);

		$this->assertSame('/content/test/project.de.txt', $translation->contentFile());
	}

	/**
	 * @covers ::exists
	 */
	public function testExists()
	{
		$page = new Page(['slug' => 'test']);

		$translation = new ContentTranslation([
			'parent' => $page,
			'code'   => 'de',
		]);

		$this->assertFalse($translation->exists());


		$translation = new ContentTranslation([
			'parent'  => $page,
			'code'    => 'de',
			'content' => ['a' => 'A']
		]);

		$this->assertTrue($translation->exists());
	}

	/**
	 * @covers ::isDefault
	 */
	public function testIsDefault()
	{
		$app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'project'
		]);

		$translation = new ContentTranslation([
			'parent' => $page,
			'code'   => 'en',
		]);

		$this->assertTrue($translation->isDefault());

		$translation = new ContentTranslation([
			'parent' => $page,
			'code'   => 'de',
		]);

		$this->assertFalse($translation->isDefault());
	}

	/**
	 * @covers ::update
	 */
	public function testUpdate()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$translation = new ContentTranslation([
			'parent'  => $page,
			'code'    => 'de',
			'content' => [
				'title' => 'test',
				'text'  => 'text'
			]
		]);

		$this->assertSame(
			$translation,
			$translation->update(['TeXt' => 'new text', 'datE' => '2042-01-01'])
		);
		$this->assertSame([
			'title' => 'test',
			'text'  => 'new text',
			'date'  => '2042-01-01'
		], $translation->content());

		$this->assertSame(
			$translation,
			$translation->update(['TeXt' => 'very new text'], true)
		);
		$this->assertSame([
			'text' => 'very new text'
		], $translation->content());
	}

	/**
	 * @covers ::__debugInfo
	 * @covers ::toArray
	 */
	public function testToArrayAndDebugInfo()
	{
		$page = new Page(['slug' => 'test']);

		$translation = new ContentTranslation([
			'parent'  => $page,
			'code'    => 'de',
			'content' => $content = ['a' => 'A']
		]);

		$expected = [
			'code'    => 'de',
			'content' => $content,
			'exists'  => true,
			'slug'    => null
		];

		$this->assertSame($expected, $translation->toArray());
		$this->assertSame($expected, $translation->__debugInfo());
	}
}
