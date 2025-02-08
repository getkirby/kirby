<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileChangeTemplateDialog::class)]
class FileChangeTemplateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.FileChangeTemplateDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						[
							'type'     => 'files',
							'template' => 'cover'
						],
						[
							'type'     => 'files',
							'template' => 'hero'
						]
					]
				],
				'files/cover' => [
					'title' => 'Cover'
				],
				'files/hero' => [
					'title' => 'hero'
				],
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
						'files'    => [
							[
								'filename' => 'test.jpg',
								'content'  => [
									'template' => 'cover'
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
		$dialog = FileChangeTemplateDialog::for('pages/test', 'test.jpg');
		$this->assertInstanceOf(FileChangeTemplateDialog::class, $dialog);
		$this->assertSame($this->app->page('test')->file(), $dialog->file());
	}

	public function testProps(): void
	{
		$dialog = FileChangeTemplateDialog::for('pages/test', 'test.jpg');
		$props  = $dialog->props();
		$this->assertArrayHasKey('template', $props['fields']);
		$this->assertSame('Change', $props['submitButton']['text']);
		$this->assertSame('cover', $props['value']['template']);
	}

	public function testRender(): void
	{
		$dialog = FileChangeTemplateDialog::for('pages/test', 'test.jpg');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'template' => 'hero'
				]
			]
		]);

		$page   = $this->app->page('test');
		$dialog = FileChangeTemplateDialog::for('pages/test', 'test.jpg');
		$this->assertSame('cover', $dialog->file()->template());

		$result = $dialog->submit();
		$this->assertSame('hero', $dialog->file()->template());
		$this->assertSame('file.changeTemplate', $result['event']);
	}
}
