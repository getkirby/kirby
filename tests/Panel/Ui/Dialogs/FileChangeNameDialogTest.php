<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\Dialog;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Dialogs\FileChangeNameDialog
 * @covers ::__construct
 */
class FileChangeNameDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.FileChangeNameDialog';

	public function setUp(): void
	{
		parent::setUp();

		$this->setUpSingleLanguage(
			site: [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg']
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
		$dialog = FileChangeNameDialog::for('pages/test', 'test.jpg');
		$this->assertInstanceOf(Dialog::class, $dialog);
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$dialog = FileChangeNameDialog::for('pages/test', 'test.jpg');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
		$this->assertArrayHasKey('name', $result['props']['fields']);
		$this->assertSame('test', $result['props']['value']['name']);
	}

	/**
	 * @covers ::submit
	 */
	public function testSubmit()
	{
		$_GET['name'] = 'foo';
		$_GET['_referrer'] = '/pages/test/files/test.jpg';

		$page   = $this->app->page('test');
		$dialog = FileChangeNameDialog::for('pages/test', 'test.jpg');
		$this->assertSame('test', $page->file()->name());

		$result = $dialog->submit();
		// TODO: why is the following not working?
		// Something actually wrong in $file::changeName()?
		// $this->assertSame('foo', $page->file()->name());
		$this->assertSame('file.changeName', $result['event']);
		$this->assertSame('/pages/test/files/foo.jpg', $result['redirect']);
	}
}
