<?php

namespace Kirby\Cms;

use Kirby\Template\Template;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class PageTemplateTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PageTemplate';

	public function testIntendedTemplate(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$this->assertInstanceOf(Template::class, $page->intendedTemplate());
		$this->assertSame('test', $page->intendedTemplate()->name());
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
