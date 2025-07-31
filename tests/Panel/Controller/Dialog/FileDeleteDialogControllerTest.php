<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Dialogs\RemoveDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileDialogController::class)]
#[CoversClass(FileDeleteDialogController::class)]
class FileDeleteDialogControllerTest extends FileDialogControllerTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.FileDeleteDialogController';
	public const CONTROLLER = FileDeleteDialogController::class;

	protected function assertLoad(File $file): void
	{
		$controller = new FileDeleteDialogController($file);
		$dialog     = $controller->load();
		$this->assertInstanceOf(RemoveDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Do you really want to delete <br><strong>a.jpg</strong>?', $props['text']);
	}

	protected function assertSubmit(
		File $file,
		Page|Site|User|null $parent = null,
		bool|string $redirect = false
	): void {
		$this->assertCount(3, $parent->files());

		$controller = new FileDeleteDialogController($file);
		$response   = $controller->submit();

		$this->assertCount(2, $parent->files());
		$this->assertSame('file.delete', $response['event']);

		if ($redirect === false) {
			$this->assertFalse($response['redirect']);
		} else {
			$this->assertSame($redirect, $response['redirect']);
		}
	}

	public function testSubmitForPageWithReferrer(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_referrer' => '/pages/test/files/a.jpg'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$parent = $this->app->page('test');
		$file   = $parent->file('a.jpg');
		$this->assertSubmit($file, $parent, '/pages/test');
	}

	public function testSubmitForSiteWithReferrer(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_referrer' => '/site/files/a.jpg'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$parent = $this->app->site();
		$file   = $parent->file('a.jpg');
		$this->assertSubmit($file, $parent, '/site');
	}

	public function testSubmitForUserWithReferrer(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_referrer' => '/users/test/files/a.jpg'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$parent = $this->app->user('test');
		$file   = $parent->file('a.jpg');
		$this->assertSubmit($file, $parent, '/users/test');
	}
}
