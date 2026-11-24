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

	protected function setUp(): void
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

	public function testBreadcrumbWithProtectedParent(): void
	{
		// the permission cache is keyed by template and role,
		// so both need to be unique for this test
		$uuid = uuid();

		$app = $this->app->clone([
			'blueprints' => [
				'pages/secret-' . $uuid => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'a',
						'template' => 'secret-' . $uuid,
						'children' => [
							[
								'slug'  => 'b',
								'files' => [
									['filename' => 'test.jpg']
								]
							]
						]
					]
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				]
			],
			'user' => 'editor@getkirby.com'
		]);

		// the title of the protected ancestor must not leak into the breadcrumb
		$file       = $app->page('a/b')->file('test.jpg');
		$controller = new PageFileViewController($file);

		$this->assertSame([
			[
				'label' => '–',
				'title' => 'Protected'
			],
			[
				'label' => 'b',
				'link'  => '/pages/a+b',
			],
			[
				'label' => 'test.jpg',
				'link'  => '/pages/a+b/files/test.jpg',
			],
		], $controller->breadcrumb());
	}

	public function testFactoryForPageFile(): void
	{
		$controller = PageFileViewController::factory('pages/test', 'test.jpg');
		$this->assertInstanceOf(PageFileViewController::class, $controller);
		$this->assertSame($this->app->page('test')->file('test.jpg'), $controller->model());
	}
}
