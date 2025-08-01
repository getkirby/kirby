<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageFormDialogController::class)]
class LanguageFormDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.LanguageFormDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English',
				]
			],
		]);
	}

	public function testFactory(): void
	{
		$controller = LanguageFormDialogController::factory();
		$this->assertInstanceOf(LanguageFormDialogController::class, $controller);
		$this->assertNull($controller->language);

		$controller = LanguageFormDialogController::factory('en');
		$this->assertInstanceOf(LanguageFormDialogController::class, $controller);
		$this->assertSame($this->app->language('en'), $controller->language);
	}

	public function testFieldsForCreate(): void
	{
		$dialog = new LanguageFormDialogController();
		$fields = $dialog->fields();

		$this->assertArrayHasKey('name', $fields);
		$this->assertArrayHasKey('code', $fields);
		$this->assertArrayHasKey('direction', $fields);
		$this->assertArrayHasKey('locale', $fields);

		$this->assertSame('text', $fields['name']['type']);
		$this->assertSame('text', $fields['code']['type']);
		$this->assertSame('select', $fields['direction']['type']);
		$this->assertSame('text', $fields['locale']['type']);
	}

	public function testFieldsForUpdate(): void
	{
		$language = $this->app->language('en');
		$dialog   = new LanguageFormDialogController($language);
		$fields   = $dialog->fields();

		$this->assertArrayHasKey('name', $fields);
		$this->assertArrayHasKey('code', $fields);
		$this->assertArrayHasKey('direction', $fields);
		$this->assertArrayHasKey('locale', $fields);

		$this->assertSame('text', $fields['name']['type']);
		$this->assertSame('text', $fields['code']['type']);
		$this->assertTrue($fields['code']['disabled']);
		$this->assertSame('select', $fields['direction']['type']);
		$this->assertSame('text', $fields['locale']['type']);
	}

	public function testFieldsForUpdateComplexLocale(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English',
					'locale'  => [LC_ALL => 'en', LC_TIME => 'en_GB'],
				]
			],
		]);

		$language = $this->app->language('en');
		$dialog   = new LanguageFormDialogController($language);
		$fields   = $dialog->fields();

		$this->assertSame('info', $fields['locale']['type']);
	}

	public function testLoadForCreate(): void
	{
		$dialog = new LanguageFormDialogController();
		$dialog = $dialog->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);
		$this->assertSame('k-language-dialog', $dialog->component);

		$props  = $dialog->props();
		$this->assertSame('Add a new language', $props['submitButton']);
		$this->assertCount(4, $props['fields']);
		$this->assertSame(
			[
				'code'      => '',
				'direction' => 'ltr',
				'locale'    => '',
				'name'      => '',
				'rules'     => '',
			],
			$props['value']
		);
	}

	public function testLoadForUpdate(): void
	{
		$language   = $this->app->language('en');
		$controller = new LanguageFormDialogController($language);
		$dialog     = $controller->load();
		$this->assertSame('k-language-dialog', $dialog->component);

		$props  = $dialog->props();
		$this->assertSame('Save', $props['submitButton']);
		$this->assertSame(
			[
				'code'      => 'en',
				'direction' => 'ltr',
				'locale'    => 'en',
				'name'      => 'English',
				'rules'     => []
			],
			$props['value']
		);
	}

	public function testSubmitForCreate(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				]
			],
			'request' => [
				'query'  => [
					'code' => 'de',
					'name' => 'Deutsch'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$this->assertCount(1, $this->app->languages());

		$dialog = new LanguageFormDialogController();
		$result = $dialog->submit();

		$this->assertSame(['event' => 'language.create'], $result);
		$this->assertCount(2, $this->app->languages());
	}

	public function testSubmitForUpdate(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query'  => [
					'name'   => 'British English',
					'locale' => 'en_GB'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$language   = $this->app->language('en');
		$controller = new LanguageFormDialogController($language);
		$this->assertSame('English', $controller->language->name());
		$this->assertSame('en', $controller->locale());

		$response = $controller->submit();
		$this->assertSame(['event' => 'language.update'], $response);
		$this->assertSame('British English', $controller->language->name());
		$this->assertSame('en_GB', $controller->locale());
	}
}
