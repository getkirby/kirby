<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Exception\PermissionException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelPreviewViewController::class)]
#[CoversClass(SitePreviewViewController::class)]
class SitePreviewViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.SitePreviewViewController';

	public function testButtons(): void
	{
		$model      = $this->app->site();
		$controller = new SitePreviewViewController($model, 'changes');
		$buttons    = $controller->buttons();
		$this->assertCount(2, $buttons->render());
	}

	public function testFactory(): void
	{
		$controller = SitePreviewViewController::factory('site', 'changes');
		$this->assertInstanceOf(SitePreviewViewController::class, $controller);
		$this->assertSame($this->app->site(), $controller->model);
		$this->assertSame('changes', $controller->versionId);
	}

	public function testId(): void
	{
		$model      = $this->app->site();
		$controller = new SitePreviewViewController($model, 'changes');
		$this->assertSame('site.preview', $controller->id());
	}

	public function testLoad(): void
	{
		$this->app->impersonate('kirby');
		$site = $this->app->site();
		$site->createChild([
			'slug'    => 'home',
			'isDraft' => false
		]);

		$controller = new SitePreviewViewController($site, 'changes');
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
		$controller = new SitePreviewViewController($site, 'changes');
		$controller->load();
	}
}
