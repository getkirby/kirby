<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageUpdateDialog::class)]
class LanguageUpdateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.LanguageUpdateDialog';

	protected function setUp(): void
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

	protected function values(): array
	{
		return [
			'code'      => 'en',
			'direction' => 'ltr',
			'locale'    => 'en',
			'name'      => 'English',
			'rules'     => []
		];
	}

	public function testFields(): void
	{
		$language = $this->app->language('en');
		$dialog   = new LanguageUpdateDialog($language);
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

	public function testFieldsComplexLocale(): void
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
		$dialog   = new LanguageUpdateDialog($language);
		$fields   = $dialog->fields();

		$this->assertSame('info', $fields['locale']['type']);
	}

	public function testFor(): void
	{
		$dialog = LanguageUpdateDialog::for('en');
		$this->assertInstanceOf(LanguageUpdateDialog::class, $dialog);
		$this->assertSame($this->app->language('en'), $dialog->language());
	}

	public function testProps(): void
	{
		$language = $this->app->language('en');
		$dialog   = new LanguageUpdateDialog($language);
		$props    = $dialog->props();
		$this->assertSame('Save', $props['submitButton']);
		$this->assertSame($this->values(), $props['value']);
	}

	public function testRender(): void
	{
		$language = $this->app->language('en');
		$dialog   = new LanguageUpdateDialog($language);
		$result   = $dialog->render();
		$this->assertSame('k-language-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query'  => [
					'name'   => 'British English',
					'locale' => 'en_GB'
				]
			]
		]);

		$language = $this->app->language('en');
		$dialog   = new LanguageUpdateDialog($language);
		$this->assertSame('English', $dialog->language()->name());
		$this->assertSame('en', $dialog->locale());

		$result   = $dialog->submit();
		$this->assertSame(['event' => 'language.update'], $result);
		$this->assertSame('British English', $dialog->language()->name());
		$this->assertSame('en_GB', $dialog->locale());
	}

	public function testValue(): void
	{
		$language = $this->app->language('en');
		$dialog   = new LanguageUpdateDialog($language);
		$value    = $dialog->value();
		$this->assertSame($this->values(), $value);
	}
}
