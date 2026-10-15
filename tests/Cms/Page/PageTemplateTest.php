<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use Kirby\Template\Template;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class PageTemplateTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageTemplate';

	public function testIntendedTemplate(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$this->assertInstanceOf(Template::class, $page->intendedTemplate());
		$this->assertSame('test', $page->intendedTemplate()->name());
	}

	public function testIntendedTemplateFromContentFile(): void
	{
		F::write(static::TMP . '/test/article.txt', '');
		F::write(static::TMP . '/test/cover.jpg', '');
		F::write(static::TMP . '/test/cover.jpg.txt', '');

		$page = new Page([
			'slug' => 'test',
			'root' => static::TMP . '/test'
		]);

		$this->assertSame('article', $page->intendedTemplate()->name());
	}

	public function testIntendedTemplateReadsDirectoryOnce(): void
	{
		F::write(static::TMP . '/test/article.txt', '');

		$page = new Page([
			'slug' => 'test',
			'root' => static::TMP . '/test'
		]);

		$page->intendedTemplate();

		// the inventory reuses the listing read for the template
		F::write(static::TMP . '/test/cover.jpg', '');

		$this->assertSame([], $page->inventory()['files']);
	}

	public function testIntendedTemplateWithInventory(): void
	{
		F::write(static::TMP . '/test/article.txt', '');

		$page = new Page([
			'slug' => 'test',
			'root' => static::TMP . '/test'
		]);

		$page->inventory();

		// the template reuses the listing read for the inventory
		F::write(static::TMP . '/test/note.txt', '');

		$this->assertSame('article', $page->intendedTemplate()->name());
	}

	public function testIntendedTemplateWithWrongCase(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'template' => 'TEST'
		]);

		$this->assertInstanceOf(Template::class, $page->intendedTemplate());
		$this->assertSame('test', $page->intendedTemplate()->name());
	}

	public function testIntendedTemplateWithoutValue(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertInstanceOf(Template::class, $page->intendedTemplate());
		$this->assertSame('default', $page->intendedTemplate()->name());
	}

	public function testInvalidTemplateValue(): void
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug'     => 'test',
			'template' => []
		]);
	}

	public function testTemplateWithExistingTemplate(): void
	{
		$this->app = $this->app->clone([
			'templates' => [
				// the file only needs to exist,
				// it doesn't need to be a valid template
				'test' => __FILE__
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$this->assertInstanceOf(Template::class, $page->template());
		$this->assertSame('test', $page->template()->name());
	}

	public function testTemplateWithNonExistingTemplate(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$this->assertInstanceOf(Template::class, $page->template());
		$this->assertSame('default', $page->template()->name());
	}

	public function testTemplateWithoutValue(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertInstanceOf(Template::class, $page->template());
		$this->assertSame('default', $page->template()->name());
	}
}
