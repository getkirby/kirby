<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\File;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelViewController::class)]
#[CoversClass(FileViewController::class)]
class FileViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.FileViewController';

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
				],
				'files' => [
					['filename' => 'test.jpg'],
					['filename' => 'test2.png']
				]
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'files' => [
						['filename' => 'test.jpg']
					]
				]
			],
		]);

		$this->app->impersonate('kirby');
	}

	public function testBreadcrumbForSiteFile(): void
	{
		$file       = $this->app->file('test.jpg');
		$controller = new FileViewController($file);
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame([
			[
				'label' => 'test.jpg',
				'link'  => '/site/files/test.jpg',
			]
		], $breadcrumb);
	}

	public function testBreadcrumbForPageFile(): void
	{
		$file       = $this->app->page('test')->file('test.jpg');
		$controller = new FileViewController($file);
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

	public function testBreadcrumbForUserFile(): void
	{
		$file       = $this->app->user('test')->file('test.jpg');
		$controller = new FileViewController($file);
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame([
			[
				'label' => 'test@getkirby.com',
				'link'  => '/users/test',
			],
			[
				'label' => 'test.jpg',
				'link'  => '/users/test/files/test.jpg',
			],
		], $breadcrumb);
	}

	public function testButtons(): void
	{
		$file       = $this->app->file('test.jpg');
		$controller = new FileViewController($file);
		$buttons    = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(3, $buttons->render());
	}

	public function testComponent(): void
	{
		$file       = $this->app->page('test')->file('test.jpg');
		$controller = new FileViewController($file);
		$this->assertSame('k-file-view', $controller->component());
	}

	public function assertFactory(
		File $file,
		string $parent
	): void {
		$controller = FileViewController::factory($parent, 'test.jpg');
		$this->assertInstanceOf(FileViewController::class, $controller);
		$this->assertSame($file, $controller->model());
	}

	public function testFactoryForSiteFile(): void
	{
		$this->assertFactory($this->app->file('test.jpg'), 'site');
	}

	public function testFactoryForPageFile(): void
	{
		$this->assertFactory($this->app->page('test')->file('test.jpg'), 'pages/test');
	}

	public function testFactoryForUserFile(): void
	{
		$this->assertFactory($this->app->user('test')->file('test.jpg'), 'users/test');
	}

	public function testIndex(): void
	{
		$file       = $this->app->file('test.jpg');
		$controller = new FileViewController($file);
		$this->assertSame(0, $controller->index());
	}

	public function testLoad(): void
	{
		$controller = new FileViewController($this->app->file('test.jpg'));
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-file-view', $view->component);
		$this->assertSame('files', $view->search);

		$props = $view->props();
		$this->assertSame('jpg', $props['extension']);
		$this->assertSame('test.jpg', $props['filename']);
		$this->assertSame('test.jpg', $props['id']);
		$this->assertSame('image/jpeg', $props['mime']);
		$this->assertSame('image', $props['type']);

		// inherited props
		$this->assertArrayHasKey('blueprint', $props);
		$this->assertArrayHasKey('lock', $props);
		$this->assertArrayHasKey('permissions', $props);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertArrayHasKey('tabs', $props);
		$this->assertArrayHasKey('title', $props);
		$this->assertArrayHasKey('versions', $props);
	}

	public function testNext(): void
	{
		$file       = $this->app->file('test.jpg');
		$controller = new FileViewController($file);
		$this->assertSame([
			'link'  => '/site/files/test2.png',
			'title' => 'test2.png',
		], $controller->next());

		$file       = $this->app->file('test2.png');
		$controller = new FileViewController($file);
		$this->assertNull($controller->next());
	}

	public function testPrev(): void
	{
		$file       = $this->app->file('test.jpg');
		$controller = new FileViewController($file);
		$this->assertNull($controller->prev());

		$file       = $this->app->file('test2.png');
		$controller = new FileViewController($file);
		$this->assertSame([
			'link'  => '/site/files/test.jpg',
			'title' => 'test.jpg',
		], $controller->prev());
	}

	public function testTitle(): void
	{
		$file       = $this->app->file('test.jpg');
		$controller = new FileViewController($file);
		$this->assertSame('test.jpg', $controller->title());
	}
}
