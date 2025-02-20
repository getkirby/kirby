<?php

namespace Kirby\Cms;

use Kirby\Exception\NotFoundException;
use Kirby\Template\Template;
use Kirby\TestCase;

class PageTemplateTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/PageTemplateTest';

	public function setUp(): void
	{
		$this->app = new App([
			'templates' => [
				'default'               => static::FIXTURES . '/template.php',
				'default.json'          => static::FIXTURES . '/template.php',
				'default.xml'           => static::FIXTURES . '/template.php',
				'template'              => static::FIXTURES . '/template.php',
				'template.json'         => static::FIXTURES . '/template.php',
				'another-template.json' => static::FIXTURES . '/template.php'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'with-template',
						'template' => 'template'
					],
					[
						'slug' => 'without-template',
						'template' => 'does-not-exist'
					],
					[
						'slug' => 'with-another-template',
						'template' => 'another-template'
					]
				]
			]
		]);
	}

	public function testIntendedTemplate()
	{
		$page = $this->app->page('with-template');
		$this->assertInstanceOf(Template::class, $page->intendedTemplate());
		$this->assertSame('template', $page->intendedTemplate()->name());

		$page = $this->app->page('without-template');
		$this->assertInstanceOf(Template::class, $page->intendedTemplate());
		$this->assertSame('does-not-exist', $page->intendedTemplate()->name());

		$page = $this->app->page('with-another-template');
		$this->assertInstanceOf(Template::class, $page->intendedTemplate());
		$this->assertSame('another-template', $page->intendedTemplate()->name());
	}

	public function testTemplate()
	{
		$page = $this->app->page('with-template');
		$this->assertInstanceOf(Template::class, $page->template());
		$this->assertSame('template', $page->template()->name());
		$this->assertSame('html', $page->template()->type());

		$page = $this->app->page('without-template');
		$this->assertInstanceOf(Template::class, $page->template());
		$this->assertSame('default', $page->template()->name());
		$this->assertSame('html', $page->template()->type());

		$page = $this->app->page('with-another-template');
		$this->assertInstanceOf(Template::class, $page->template());
		$this->assertSame('default', $page->template()->name());
		$this->assertSame('html', $page->template()->type());
	}

	public function testRepresentation()
	{
		$page = $this->app->page('with-template');
		$representation = $page->representation('json');
		$this->assertInstanceOf(Template::class, $representation);
		$this->assertSame('template', $representation->name());
		$this->assertSame('json', $representation->type());

		$page = $this->app->page('without-template');
		$representation = $page->representation('json');
		$this->assertInstanceOf(Template::class, $representation);
		$this->assertSame('default', $representation->name());
		$this->assertSame('json', $representation->type());

		$page = $this->app->page('without-template');
		$representation = $page->representation('xml');
		$this->assertInstanceOf(Template::class, $representation);
		$this->assertSame('default', $representation->name());
		$this->assertSame('xml', $representation->type());

		$page = $this->app->page('with-another-template');
		$representation = $page->representation('xml');
		$this->assertInstanceOf(Template::class, $representation);
		$this->assertSame('default', $representation->name());
		$this->assertSame('xml', $representation->type());
	}

	public function testRepresentationError()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The content representation cannot be found');

		$page = $this->app->page('with-template');
		$page->representation('xml');
	}
}
