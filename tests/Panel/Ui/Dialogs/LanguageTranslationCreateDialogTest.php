<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Language;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageTranslationCreateDialog::class)]
class LanguageTranslationCreateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.LanguageTranslationCreateDialog';

	protected Language $language;

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

		$this->language = $this->app->language('en');
	}

	public function testFields(): void
	{
		$dialog = new LanguageTranslationCreateDialog($this->language);
		$fields = $dialog->fields();

		$this->assertArrayHasKey('key', $fields);
		$this->assertArrayHasKey('value', $fields);

		$this->assertSame('text', $fields['key']['type']);
		$this->assertSame('textarea', $fields['value']['type']);
	}

	public function testFor(): void
	{
		$dialog = LanguageTranslationCreateDialog::for('en');
		$this->assertInstanceOf(LanguageTranslationCreateDialog::class, $dialog);
		$this->assertSame($this->language, $dialog->language());
	}

	public function testProps(): void
	{
		$dialog = new LanguageTranslationCreateDialog($this->language);
		$props  = $dialog->props();
		$this->assertSame('large', $props['size']);
		$this->assertCount(2, $props['fields']);
		$this->assertSame([], $props['value']);
	}

	public function testRender(): void
	{
		$dialog = new LanguageTranslationCreateDialog($this->language);
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query'  => [
					'key'   => 'foo',
					'value' => 'bar'
				]
			]
		]);

		$this->assertNull($this->language->variable('foo')->value());

		$dialog = new LanguageTranslationCreateDialog($this->language);
		$result = $dialog->submit();

		$this->assertTrue($result);

		// retrieve new/updated language object
		$language = $this->app->language('en');

		$this->assertSame('bar', $language->variable('foo')->value());
	}

	public function testSubmitNonDefaultLanguage(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query'  => [
					'key'   => 'fö',
					'value' => 'bär'
				]
			]
		]);

		$language = $this->app->language('de');
		$this->assertNull($language->variable('fö')->value());

		$dialog = new LanguageTranslationCreateDialog($language);
		$result = $dialog->submit();

		$this->assertTrue($result);

		// retrieve new/updated language object
		$language = $this->app->language('de');

		$this->assertSame('bär', $language->variable('fö')->value());
	}
}
