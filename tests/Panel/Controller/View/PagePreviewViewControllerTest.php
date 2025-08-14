<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelPreviewViewController::class)]
#[CoversClass(PagePreviewViewController::class)]
class PagePreviewViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.PagePreviewViewController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testButtons(): void
	{
		$model      = $this->app->page('test');
		$controller = new PagePreviewViewController($model, 'changes');
		$buttons    = $controller->buttons();
		$this->assertCount(2, $buttons->render());
	}

	public function testFactory(): void
	{
		$controller = PagePreviewViewController::factory('pages/test', 'changes');
		$this->assertInstanceOf(PagePreviewViewController::class, $controller);
		$this->assertSame($this->app->page('test'), $controller->model);
		$this->assertSame('changes', $controller->versionId);
	}

	public function testId(): void
	{
		$model      = $this->app->page('test');
		$controller = new PagePreviewViewController($model, 'changes');
		$this->assertSame('page.preview', $controller->id());
	}

	public function testLoad(): void
	{
		$model      = $this->app->page('test');
		$controller = new PagePreviewViewController($model, 'changes');
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-preview-view', $view->component);
		$this->assertSame('test | Preview', $view->title);

		$props = $view->props();
		$token = $model->version('changes')->previewToken();
		$this->assertSame('/test?_token=' . $token . '&_version=changes', $props['src']['changes']);
	}
}
