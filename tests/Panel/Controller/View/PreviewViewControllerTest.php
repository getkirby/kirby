<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Exception\PermissionException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PreviewViewController::class)]
class PreviewViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.PreviewViewController';

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
		// page
		$model      = $this->app->page('test');
		$controller = new PreviewViewController($model, 'changes');
		$buttons    = $controller->buttons();
		$this->assertCount(3, $buttons->render());

		// site
		$model      = $this->app->site();
		$controller = new PreviewViewController($model, 'changes');
		$buttons    = $controller->buttons();
		$this->assertCount(2, $buttons->render());
	}

	public function testFactory(): void
	{
		$controller = PreviewViewController::factory('pages/test', 'changes');
		$this->assertInstanceOf(PreviewViewController::class, $controller);
		$this->assertSame($this->app->page('test'), $controller->model);
		$this->assertSame('changes', $controller->mode);

		$controller = PreviewViewController::factory('site', 'changes');
		$this->assertInstanceOf(PreviewViewController::class, $controller);
		$this->assertSame($this->app->site(), $controller->model);
	}

	public function testId(): void
	{
		$model      = $this->app->page('test');
		$controller = new PreviewViewController($model, 'changes');
		$this->assertSame('page.preview', $controller->id());
	}

	public function testLoad(): void
	{
		$model      = $this->app->page('test');
		$controller = new PreviewViewController($model, 'changes');
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-preview-view', $view->component);
		$this->assertSame('test | Preview', $view->title);

		$props = $view->props();
		$token = $model->version('changes')->previewToken();
		$this->assertSame('/test?_token=' . $token . '&_version=changes&_preview=true', $props['src']['changes']);
	}

	public function testLoadInvalid(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The preview is not available');
		$site       = $this->app->site();
		$controller = new PreviewViewController($site, 'changes');
		$controller->load();
	}

	public function testRedirect(): void
	{
		$model      = $this->app->page('test');
		$controller = new PreviewViewController($model, 'latest');
		$redirect   = $controller->redirect('latest');
		$this->assertSame(null, $redirect);

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'notes']
				]
			],
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/notes/page:2?foo=bar&_preview=true'
				],
			]
		]);

		$controller = new PreviewViewController($model, 'latest');
		$redirect   = $controller->redirect();
		$this->assertSame('/panel/pages/notes/preview/latest?_query=foo%3Dbar&_params=page%3A2', $redirect);
	}

	public function testSrc(): void
	{
		$model      = $this->app->page('test');
		$token      = $model->version('changes')->previewToken();
		$controller = new PreviewViewController($model, 'compare');
		$src        = $controller->src();

		$this->assertSame('/test?_preview=true', $src['latest']);
		$this->assertSame('/test?_token=' . $token . '&_version=changes&_preview=true', $src['changes']);
	}

	public function testSrcBrowser(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'browser' => 'https://getkirby.com/notes/page:2?foo=bar&_preview=true'
				],
			]
		]);

		$this->app->impersonate('kirby');
		$this->app->site()->createChild([
			'slug' => 'notes'
		]);

		$token      = $this->app->page('notes')->version('changes')->previewToken();
		$controller = new PreviewViewController($this->app->page('test'), 'changes');
		$src        = $controller->src();

		$this->assertSame('/notes/page:2?_token=' . $token . '&_version=changes&foo=bar&_preview=true', $src['changes']);
	}

	public function testSrcRedirectParamsQuery(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_params' => 'foo:bar',
					'_query'  => 'foo=bar'
				]
			]
		]);

		$this->app->impersonate('kirby');
		$model      = $this->app->page('test');
		$token      = $model->version('changes')->previewToken();
		$controller = new PreviewViewController($model, 'compare');
		$src        = $controller->src();

		$this->assertSame('/test/foo:bar?_preview=true&foo=bar', $src['latest']);
		$this->assertSame('/test/foo:bar?_token=' . $token . '&_version=changes&_preview=true&foo=bar', $src['changes']);
	}
}
