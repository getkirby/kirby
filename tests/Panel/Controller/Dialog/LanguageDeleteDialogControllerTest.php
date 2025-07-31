<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialogs\RemoveDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageDeleteDialogController::class)]
class LanguageDeleteDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.LanguageDeleteDialogController';

	public function setUp(): void
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

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$dialog = LanguageDeleteDialogController::factory('de');
		$this->assertInstanceOf(LanguageDeleteDialogController::class, $dialog);
		$this->assertSame($this->app->language('de'), $dialog->language);
	}

	public function testLoad(): void
	{
		$language = $this->app->language('de');
		$dialog   = new LanguageDeleteDialogController($language);

		$dialog = $dialog->load();
		$this->assertInstanceOf(RemoveDialog::class, $dialog);
		$this->assertSame('k-remove-dialog', $dialog->component);


		$props = $dialog->props();
		$this->assertSame('Do you really want to delete the language <strong>Deutsch</strong> including all translations? This cannot be undone!', $props['text']);
	}
	public function testSubmit(): void
	{
		$this->assertCount(2, $this->app->languages());

		$language = $this->app->language('de');
		$dialog   = new LanguageDeleteDialogController($language);
		$response = $dialog->submit();

		$this->assertSame('language.delete', $response['event']);
		$this->assertSame('languages', $response['redirect']);
		$this->assertCount(1, $this->app->languages());
	}
}
