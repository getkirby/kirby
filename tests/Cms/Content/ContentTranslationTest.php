<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class ContentTranslationTest extends TestCase
{
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
