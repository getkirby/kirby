<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\Dialog;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Dialogs\FileChangeSortDialog
 * @covers ::__construct
 */
class FileChangeSortDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.FileChangeSortDialog';

	public function setUp(): void
	{
		parent::setUp();

		$this->setUpSingleLanguage(
			site: [
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
								'filename' => 'b.jpg',
								'content'  => [
									'sort' => 2
								]
							],
							[
								'filename' => 'c.jpg',
								'content'  => [
									'sort' => 3
								]
							]
						]
					]
				]
			]
		);

		$this->app->impersonate('kirby');
	}

	/**
	 * @covers ::for
	 */
	public function testFor()
	{
		$dialog = FileChangeSortDialog::for('pages/test', 'a.jpg');
		$this->assertInstanceOf(Dialog::class, $dialog);
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$dialog = FileChangeSortDialog::for('pages/test', 'a.jpg');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
		$this->assertArrayHasKey('position', $result['props']['fields']);
		$this->assertSame(1, $result['props']['value']['position']);
	}

	/**
	 * @covers ::submit
	 */
	public function testSubmit()
	{
		$_GET['position'] = 3;

		$page   = $this->app->page('test');
		$dialog = FileChangeSortDialog::for('pages/test', 'a.jpg');
		$this->assertSame(1, $page->file()->sort()->value());

		$result = $dialog->submit();
		$this->assertSame(3, $page->file()->sort()->value());
		$this->assertSame('file.sort', $result['event']);
	}
}
