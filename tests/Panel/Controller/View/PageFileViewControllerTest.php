<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelViewController::class)]
#[CoversClass(FileViewController::class)]
#[CoversClass(PageFileViewController::class)]
class PageFileViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.PageFileViewController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testBreadcrumb(): void
	{
		$file       = $this->app->page('test')->file('test.jpg');
		$controller = new PageFileViewController($file);
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame([
			[
				'label' => 'test',
				'link'  => '/pages/test',
			],
			[
				'label' => 'test.jpg',
				'link'  => '/pages/test/files/test.jpg',
			],
		], $breadcrumb);
	}

	public function testFactoryForPageFile(): void
	{
		$controller = PageFileViewController::factory('pages/test', 'test.jpg');
		$this->assertInstanceOf(PageFileViewController::class, $controller);
		$this->assertSame($this->app->page('test')->file('test.jpg'), $controller->model());
	}
}
