<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SiteChangeTitleDialogController::class)]
class SiteChangeTitleDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.SiteChangeTitleDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'content' => [
					'title' => 'My Site'
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testLoad(): void
	{
		$controller = new SiteChangeTitleDialogController();
		$dialog     = $controller->load();

		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertArrayHasKey('title', $props['fields']);
		$this->assertSame('My Site', $props['value']['title']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'My Other Site',
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new SiteChangeTitleDialogController();
		$this->assertSame('My Site', $this->app->site()->title()->value());

		$response = $controller->submit();
		$this->assertSame('My Other Site', $this->app->site()->title()->value());
		$this->assertSame('site.changeTitle', $response['event']);
	}
}
