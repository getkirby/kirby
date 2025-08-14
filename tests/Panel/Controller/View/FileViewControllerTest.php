<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelViewController::class)]
#[CoversClass(FileViewController::class)]
class FileViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.FileViewController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'test.jpg'],
					['filename' => 'test2.png']
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testBreadcrumb(): void
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
		$file       = $this->app->file('test.jpg');
		$controller = new FileViewController($file);
		$this->assertSame('k-file-view', $controller->component());
	}

	public function testFactoryForSiteFile(): void
	{
		$controller = FileViewController::factory('site', 'test.jpg');
		$this->assertInstanceOf(FileViewController::class, $controller);
		$this->assertSame($this->app->file('test.jpg'), $controller->model());
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
