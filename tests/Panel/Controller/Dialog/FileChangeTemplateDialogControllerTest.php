<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileDialogController::class)]
#[CoversClass(FileChangeTemplateDialogController::class)]
class FileChangeTemplateDialogControllerTest extends FileDialogControllerTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.FileChangeTemplateDialogController';
	public const string CONTROLLER = FileChangeTemplateDialogController::class;

	protected function assertLoad(File $file): void
	{
		$controller = new FileChangeTemplateDialogController($file);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Template', $props['fields']['template']['label']);
		$this->assertSame('select', $props['fields']['template']['type']);
		$this->assertSame('Change', $props['submitButton']['text']);
		$this->assertSame('notice', $props['submitButton']['theme']);
		$this->assertSame('a', $props['value']['template']);
	}

	protected function assertSubmit(
		File $file,
		Page|Site|User|null $parent = null,
		bool|string $redirect = false
	): void {
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'template' => 'a'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new FileChangeTemplateDialogController($file);
		$response   = $controller->submit();

		$this->assertSame('file.changeTemplate', $response['event']);
	}
}
