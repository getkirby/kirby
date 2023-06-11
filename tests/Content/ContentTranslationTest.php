<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Content\ContentTranslation
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

		$this->assertSame($page, $translation->parent());
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
	 * @covers ::contentFile
	 */
	public function testContentFile()
	{
		$app = new App([
			'roots' => [
				'content' => '/content',
			]
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
