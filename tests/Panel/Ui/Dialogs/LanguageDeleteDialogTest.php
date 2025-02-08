<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageDeleteDialog::class)]
class LanguageDeleteDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.LanguageDeleteDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				],
				'de' => [
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			],
		]);
	}

	public function testFor(): void
	{
		$dialog = LanguageDeleteDialog::for('de');
		$this->assertInstanceOf(LanguageDeleteDialog::class, $dialog);
		$this->assertSame($this->app->language('de'), $dialog->language());
	}

	public function testProps(): void
	{
		$language = $this->app->language('de');
		$dialog   = new LanguageDeleteDialog($language);
		$props    = $dialog->props();
		$this->assertSame('Do you really want to delete the language <strong>Deutsch</strong> including all translations? This cannot be undone!', $props['text']);
	}

	public function testRender(): void
	{
		$language = $this->app->language('de');
		$dialog   = new LanguageDeleteDialog($language);
		$result   = $dialog->render();
		$this->assertSame('k-remove-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->assertCount(2, $this->app->languages());

		$language = $this->app->language('de');
		$dialog   = new LanguageDeleteDialog($language);
		$result   = $dialog->submit();

		$this->assertSame('language.delete', $result['event']);
		$this->assertSame('languages', $result['redirect']);
		$this->assertCount(1, $this->app->languages());
	}
}
