<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileChangeSortDialog::class)]
class FileChangeSortDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.FileChangeSortDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => [
									'sort' => 1
								]
							],
							[
								'filename' => 'b.jpg'
							],
							[
								'filename' => 'c.jpg',
								'content'  => [
									'sort' => 2
								]
							]
						]
					]
				]
			]
		]);
	}

	public function testFor(): void
	{
		$dialog = FileChangeSortDialog::for('pages/test', 'a.jpg');
		$this->assertInstanceOf(FileChangeSortDialog::class, $dialog);
		$this->assertSame($this->app->page('test')->file(), $dialog->file());
	}

	public function testProps(): void
	{
		$dialog = FileChangeSortDialog::for('pages/test', 'a.jpg');
		$props   = $dialog->props();
		$this->assertArrayHasKey('position', $props['fields']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame(1, $props['value']['position']);
	}

	public function testPropsForFileWithoutSortContentField(): void
	{
		$dialog = FileChangeSortDialog::for('pages/test', 'b.jpg');
		$props   = $dialog->props();
		$this->assertArrayHasKey('position', $props['fields']);
		$this->assertSame(3, $props['value']['position']);
	}

	public function testRender(): void
	{
		$dialog = FileChangeSortDialog::for('pages/test', 'a.jpg');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'position' => 3
				]
			]
		]);

		$page   = $this->app->page('test');
		$dialog = FileChangeSortDialog::for('pages/test', 'a.jpg');
		$this->assertSame(1, $page->file()->sort()->value());

		$result = $dialog->submit();
		$this->assertSame(3, $page->file()->sort()->value());
		$this->assertSame('file.sort', $result['event']);
	}
}
