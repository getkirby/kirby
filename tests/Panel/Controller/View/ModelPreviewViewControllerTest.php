<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Exception\PermissionException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;

class ModelPreviewViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.ModelPreviewViewController';

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

	public function testButtonsForPage(): void
	{
		$model      = $this->app->page('test');
		$controller = new ModelPreviewViewController($model, 'changes');
		$buttons    = $controller->buttons();
		$this->assertCount(2, $buttons->render());
	}

	public function testButtonsForSite(): void
	{
		$model      = $this->app->site();
		$controller = new ModelPreviewViewController($model, 'changes');
		$buttons    = $controller->buttons();
		$this->assertCount(2, $buttons->render());
	}

	public function testFactoryForPage(): void
	{
		$controller = ModelPreviewViewController::factory('pages/test', 'changes');
		$this->assertInstanceOf(ModelPreviewViewController::class, $controller);
		$this->assertSame($this->app->page('test'), $controller->model);
		$this->assertSame('changes', $controller->versionId);
	}

	public function testFactoryForSite(): void
	{
		$controller = ModelPreviewViewController::factory('site', 'changes');
		$this->assertInstanceOf(ModelPreviewViewController::class, $controller);
		$this->assertSame($this->app->site(), $controller->model);
		$this->assertSame('changes', $controller->versionId);
	}

	public function testIdForPage(): void
	{
		$model      = $this->app->page('test');
		$controller = new ModelPreviewViewController($model, 'changes');
		$this->assertSame('page.preview', $controller->id());
	}

	public function testIdForSite(): void
	{
		$model      = $this->app->site();
		$controller = new ModelPreviewViewController($model, 'changes');
		$this->assertSame('site.preview', $controller->id());
	}

	public function testLoadForPage(): void
	{
		$model      = $this->app->page('test');
		$controller = new ModelPreviewViewController($model, 'changes');
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-preview-view', $view->component);
		$this->assertSame('test | Preview', $view->title);

		$props = $view->props();
		$token = $model->version('changes')->previewToken();
		$this->assertSame('/test?_token=' . $token . '&_version=changes', $props['src']['changes']);
	}

	public function testLoadForSite(): void
	{
		$site = $this->app->site();
		$site->createChild([
			'slug'    => 'home',
			'isDraft' => false
		]);

		$controller = new ModelPreviewViewController($site, 'changes');
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-preview-view', $view->component);
		$this->assertSame('Site | Preview', $view->title);

		$props = $view->props();
		$token = $site->version('changes')->previewToken();
		$this->assertSame('/?_token=' . $token . '&_version=changes', $props['src']['changes']);
	}

	public function testLoadInvalid(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The preview is not available');
		$site       = $this->app->site();
		$controller = new ModelPreviewViewController($site, 'changes');
		$controller->load();
	}
}
