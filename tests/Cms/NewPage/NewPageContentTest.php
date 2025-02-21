<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Content\Content;
use Kirby\Content\MemoryStorage;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class NewPageContentTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageContent';

	public function testContent(): void
	{
		$page = new Page(['slug' => 'test']);
		$this->assertInstanceOf(Content::class, $page->content());
		$this->assertSame([], $page->content()->toArray());
	}

	public function testSetContentWithInvalidValue(): void
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug'    => 'test',
			'content' => 'content'
		]);
	}

	public function testSetContentWithChaoticFieldNames(): void
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'fields' => [
					'lowercase' => [
						'type' => 'text',
					],
					'UPPERCASE' => [
						'type' => 'text',
					],
					'camelCase' => [
						'type' => 'text',
					],
					'under_scored' => [
						'type' => 'text',
					],
					'with-dashes' => [
						'type' => 'text',
					]
				]
			],
			'content' => [
				'lowercase'    => 'lowercase',
				'UPPERCASE'    => 'UPPERCASE',
				'camelCase'    => 'camelCase',
				'under_scored'  => 'under_scored',
				'with-dashes'  => 'with-dashes',
			]
		]);

		$this->assertSame([
			'lowercase'    => 'lowercase',
			'uppercase'    => 'UPPERCASE',
			'camelcase'    => 'camelCase',
			'under_scored' => 'under_scored',
			'with-dashes'  => 'with-dashes',
		], $page->content()->toArray());
	}

	public function testSetContentInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => $content = [
				'title'    => 'Title 1',
				'subtitle' => 'Subtitle 1'
			]
		]);

		$this->assertInstanceOf(MemoryStorage::class, $page->storage());

		$this->assertSame($content, $page->content()->toArray());

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.txt');
	}

	public function testSetContentInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
			'content' => $content = [
				'title'    => 'Title 1',
				'subtitle' => 'Subtitle 1'
			]
		]);

		$this->assertInstanceOf(MemoryStorage::class, $page->storage());

		$this->assertSame($content, $page->content()->toArray());
		$this->assertSame($content, $page->content('en')->toArray());
		$this->assertSame($content, $page->content('de')->toArray());

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.en.txt');
	}
}
