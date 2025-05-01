<?php

namespace Kirby\Cms;

use Kirby\Exception\NotFoundException;
use Kirby\Template\Template;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageRepresentationTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageRepresentation';

	public function testRepresentationWithExistingTemplate(): void
	{
		$this->app = $this->app->clone([
			'templates' => [
				'test'      => __FILE__,
				'test.json' => __FILE__
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$representation = $page->representation('json');
		$this->assertInstanceOf(Template::class, $representation);
		$this->assertSame('test', $representation->name());
		$this->assertSame('json', $representation->type());
	}

	public function testRepresentationWithFallbackToDefaultRepresentation(): void
	{
		$this->app = $this->app->clone([
			'templates' => [
				'default'      => __FILE__,
				'default.json' => __FILE__
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$representation = $page->representation('json');
		$this->assertInstanceOf(Template::class, $representation);
		$this->assertSame('default', $representation->name());
		$this->assertSame('json', $representation->type());
	}

	public function testRepresentationWithMultipleTypes(): void
	{
		$this->app = $this->app->clone([
			'templates' => [
				'test'      => __FILE__,
				'test.xml'  => __FILE__,
				'test.json' => __FILE__
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$representation = $page->representation('xml');
		$this->assertInstanceOf(Template::class, $representation);
		$this->assertSame('test', $representation->name());
		$this->assertSame('xml', $representation->type());

		$representation = $page->representation('json');
		$this->assertInstanceOf(Template::class, $representation);
		$this->assertSame('test', $representation->name());
		$this->assertSame('json', $representation->type());
	}

	public function testRepresentationWithMissingRepresentation(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The content representation cannot be found');

		$page->representation('json');
	}
}
