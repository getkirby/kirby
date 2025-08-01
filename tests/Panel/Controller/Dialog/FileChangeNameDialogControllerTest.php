<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileDialogController::class)]
#[CoversClass(FileChangeNameDialogController::class)]
class FileChangeNameDialogControllerTest extends FileDialogControllerTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.FileChangeNameDialogController';
	public const CONTROLLER = FileChangeNameDialogController::class;

	protected function assertLoad(File $file): void
	{
		$controller = new FileChangeNameDialogController($file);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Name', $props['fields']['name']['label']);
		$this->assertSame('slug', $props['fields']['name']['type']);
		$this->assertSame('Rename', $props['submitButton']);
		$this->assertSame('a', $props['value']['name']);
	}

	protected function assertSubmit(
		File $file,
		Page|Site|User|null $parent = null,
		bool|string $redirect = false
	): void {
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'name' => 'new-test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new FileChangeNameDialogController($file);
		$response   = $controller->submit();

		$this->assertSame('new-test', $parent->file('new-test.jpg')->name());
		$this->assertSame('file.changeName', $response['event']);

		if ($redirect === true) {
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
		$this->assertSubmit($file, $parent, '/pages/test/files/new-test.jpg');
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
		$this->assertSubmit($file, $parent, '/site/files/new-test.jpg');
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
		$this->assertSubmit($file, $parent, '/users/test/files/new-test.jpg');
	}
}
