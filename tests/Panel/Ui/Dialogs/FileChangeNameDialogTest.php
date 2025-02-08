<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileChangeNameDialog::class)]
class FileChangeNameDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.FileChangeNameDialog';

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
		$dialog = FileChangeNameDialog::for('pages/test', 'test.jpg');
		$this->assertInstanceOf(FileChangeNameDialog::class, $dialog);
		$this->assertSame($this->app->page('test')->file(), $dialog->file());
	}

	public function testProps(): void
	{
		$dialog = FileChangeNameDialog::for('pages/test', 'test.jpg');
		$props  = $dialog->props();
		$this->assertArrayHasKey('name', $props['fields']);
		$this->assertSame('Rename', $props['submitButton']);
		$this->assertSame('test', $props['value']['name']);
	}

	public function testRender(): void
	{
		$dialog = FileChangeNameDialog::for('pages/test', 'test.jpg');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'name'      => 'foo',
					'_referrer' => '/pages/test/files/test.jpg'
				]
			]
		]);

		$dialog = FileChangeNameDialog::for('pages/test', 'test.jpg');
		$this->assertSame('test', $dialog->file()->name());

		$result = $dialog->submit();
		$this->assertSame('foo', $dialog->file()->name());
		$this->assertSame('file.changeName', $result['event']);
		$this->assertSame('/pages/test/files/foo.jpg', $result['redirect']);
	}
}
