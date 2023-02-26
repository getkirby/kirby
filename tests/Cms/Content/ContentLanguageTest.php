<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\ContentLanguage
 */
class ContentLanguageTest extends TestCase
{
	/**
	 * @covers ::code
	 * @covers ::parent
	 */
	public function testParentAndCode()
	{
		$page     = new Page(['slug' => 'test']);
		$language = new ContentLanguage(
			parent: $page,
			code:   'de'
		);

		$this->assertSame($page, $language->parent());
		$this->assertSame('de', $language->code());
		$this->assertSame('de', $language->id());
	}

	/**
	 * @covers ::content
	 * @covers ::slug
	 */
	public function testContentAndSlug()
	{
		$page     = new Page(['slug' => 'test']);
		$language = new ContentLanguage(
			parent: $page,
			code:   'de',
			slug:   'test',
			content: $content = ['title' => 'test']
		);

		$this->assertSame('test', $language->slug());
		$this->assertSame($content, $language->content());
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

		$language = new ContentLanguage(
			parent: $page,
			code:   'de'
		);


		$this->assertSame(
			'/content/test/project.de.txt',
			$language->contentFile()
		);
	}

	/**
	 * @covers ::exists
	 */
	public function testExists()
	{
		$page     = new Page(['slug' => 'test']);
		$language = new ContentLanguage(
			parent: $page,
			code:   'de'
		);

		$this->assertFalse($language->exists());

		$page     = new Page(['slug' => 'test']);
		$language = new ContentLanguage(
			parent:  $page,
			code:    'de',
			content: ['a' => 'A']
		);

		$this->assertTrue($language->exists());
	}

	/**
	 * @covers ::toArray
	 * @covers ::__debugInfo
	 */
	public function testToArrayAndDebugInfo()
	{
		$page     = new Page(['slug' => 'test']);
		$language = new ContentLanguage(
			parent:  $page,
			code:    'de',
			content: $content = ['a' => 'A']
		);

		$expected = [
			'code'    => 'de',
			'content' => $content,
			'exists'  => true,
			'slug'    => null
		];

		$this->assertSame($expected, $language->toArray());
		$this->assertSame($expected, $language->__debugInfo());
	}
}
