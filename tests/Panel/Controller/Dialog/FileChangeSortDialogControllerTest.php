<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Filesystem\F;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileDialogController::class)]
#[CoversClass(FileChangeSortDialogController::class)]
class FileChangeSortDialogControllerTest extends FileDialogControllerTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.FileChangeSortDialogController';
	public const string CONTROLLER = FileChangeSortDialogController::class;

	protected function assertLoad(File $file): void
	{
		$controller = new FileChangeSortDialogController($file);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Change position', $props['fields']['position']['label']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame(3, $props['value']['position']);
	}

	protected function assertSubmit(
		File $file,
		Page|Site|User|null $parent = null,
		bool|string $redirect = false
	): void {
		// pretend the files exists
		F::write($parent->file('a.jpg')->root(), '');
		F::write($parent->file('b.jpg')->root(), '');
		F::write($parent->file('c.jpg')->root(), '');

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'position' => 2
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new FileChangeSortDialogController($file);
		$response   = $controller->submit();

		$this->assertSame('file.sort', $response['event']);
		$this->assertSame(1, $parent->file('b.jpg')->sort()->toInt());
		$this->assertSame(2, $parent->file('a.jpg')->sort()->toInt());
		$this->assertSame(3, $parent->file('c.jpg')->sort()->toInt());
	}
}
