<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileDeleteDialog::class)]
class FileDeleteDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.FileDeleteDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);
	}

	public function testFor(): void
	{
		$dialog = FileDeleteDialog::for('pages/test', 'test.jpg');
		$this->assertInstanceOf(FileDeleteDialog::class, $dialog);
		$this->assertSame($this->app->page('test')->file(), $dialog->file());
	}

	public function testProps(): void
	{
		$dialog = FileDeleteDialog::for('pages/test', 'test.jpg');
		$props  = $dialog->props();
		$this->assertSame('Do you really want to delete <br><strong>test.jpg</strong>?', $props['text']);
	}

	public function testRender(): void
	{
		$dialog = FileDeleteDialog::for('pages/test', 'test.jpg');
		$result = $dialog->render();
		$this->assertSame('k-remove-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_referrer' => '/pages/test/files/test.jpg'
				]
			]
		]);

		$this->assertCount(1, $this->app->page('test')->files());
		$dialog = FileDeleteDialog::for('pages/test', 'test.jpg');
		$this->assertSame('test', $dialog->file()->name());

		$result = $dialog->submit();
		$this->assertCount(0, $this->app->page('test')->files());
		$this->assertSame('file.delete', $result['event']);
		$this->assertSame('/pages/test', $result['redirect']);
	}
}
