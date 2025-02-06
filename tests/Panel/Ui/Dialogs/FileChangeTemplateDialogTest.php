<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Panel\Ui\Dialog;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Dialogs\FileChangeTemplateDialog
 * @covers ::__construct
 */
class FileChangeTemplateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.FileChangeTemplateDialog';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = new App([
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
			'roots' => [
				'index' => static::TMP
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

		$this->app->impersonate('kirby');
	}

	/**
	 * @covers ::for
	 */
	public function testFor()
	{
		$dialog = FileChangeTemplateDialog::for('pages/test', 'test.jpg');
		$this->assertInstanceOf(Dialog::class, $dialog);
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$dialog = FileChangeTemplateDialog::for('pages/test', 'test.jpg');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
		$this->assertArrayHasKey('template', $result['props']['fields']);
		$this->assertSame('cover', $result['props']['value']['template']);
	}

	/**
	 * @covers ::submit
	 */
	public function testSubmit()
	{
		$_GET['template'] = 'hero';

		$page   = $this->app->page('test');
		$dialog = FileChangeTemplateDialog::for('pages/test', 'test.jpg');
		$this->assertSame('cover', $page->file()->template());

		$result = $dialog->submit();
		$this->assertSame('hero', $page->file()->template());
		$this->assertSame('file.changeTemplate', $result['event']);
	}
}
