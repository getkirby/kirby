<?php

namespace Kirby\Cms;


use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageControllerTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageController';

	public function testControllerForACustomTemplate(): void
	{
		F::write(static::TMP . '/template.php', 'Test Template');

		$this->app = $this->app->clone([
			'templates' => [
				'test' => static::TMP . '/template.php',
			],
			'controllers' => [
				'test' => function (Page $page) {
					return [
						'title' => 'New ' . $page->title(),
					];
				},
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test',
			'content'  => [
				'title' => 'Test Title',
			]
		]);

		$data = $page->controller();

		$this->assertSame($this->app, $data['kirby']);
		$this->assertSame($this->app->site(), $data['site']);
		$this->assertSame($this->app->site()->pages(), $data['pages']);
		$this->assertSame($page, $data['page']);
		$this->assertSame('New Test Title', $data['title']);
	}

	public function testControllerForTheDefaultTemplate(): void
	{
		$this->app = $this->app->clone([
			'controllers' => [
				'default' => function (Page $page) {
					return [
						'title' => 'New ' . $page->title(),
					];
				},
			]
		]);

		$page = new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Test Title',
			]
		]);

		$data = $page->controller();

		$this->assertSame('New Test Title', $data['title']);
	}

	public function testControllerForTheSite(): void
	{
		$this->app = $this->app->clone([
			'controllers' => [
				'site' => function (Page $page) {
					return [
						'title' => 'New ' . $page->title(),
					];
				},
				'default' => function (Page $page) {
					return [
						'subtitle' => 'New Subtitle for: ' . $page->slug(),
					];
				}
			]
		]);

		$page = new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Test Title',
			]
		]);

		$data = $page->controller();

		$this->assertSame('New Test Title', $data['title']);
		$this->assertSame('New Subtitle for: test', $data['subtitle']);
	}

	public function testControllerWithInvalidPageObject(): void
	{
		$this->app = $this->app->clone([
			'controllers' => [
				'default' => fn ($page) => ['page' => 'string'],
			]
		]);

		$page = new Page([
			'slug' => 'test',
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The returned variable "page" from the controller "default" is not of the required type "Kirby\Cms\Page"');

		$page->controller();
	}
}
