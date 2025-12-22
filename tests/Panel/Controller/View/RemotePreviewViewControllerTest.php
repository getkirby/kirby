<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RemotePreviewViewController::class)]
class RemotePreviewViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.RemotePreviewViewController';

	public function testLoad(): void
	{
		$this->app->impersonate('kirby');
		$site = $this->app->site();
		$site->createChild([
			'slug'    => 'home',
			'isDraft' => false
		]);

		$controller = new RemotePreviewViewController($site, 'changes');
		$view       = $controller->load();
		$this->assertSame('k-remote-preview-view', $view->component);
		$this->assertSame('Preview', $view->title);
	}

	public function testRedirect(): void
	{
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

		$model      = $this->app->page('notes');
		$controller = new RemotePreviewViewController($model, 'form');
		$redirect   = $controller->redirect();
		$this->assertSame('/panel/pages/notes/preview/form/remote?_query=foo%3Dbar&_params=page%3A2', $redirect);
	}
}
