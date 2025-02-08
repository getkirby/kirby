<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Exception\NotFoundException;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageTranslationUpdateDialog::class)]
class LanguageTranslationUpdateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.LanguageTranslationUpdateDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'         => 'en',
					'default'      => true,
					'name'         => 'English',
					'translations' => [
						'normal'   => 'test',
						'variable' => ['one', 'two', 'three']
					]
				]
			],
		]);
	}

	public function testFields(): void
	{
		$variable = $this->app->language('en')->variable('normal');
		$dialog   = new LanguageTranslationUpdateDialog($variable);
		$fields   = $dialog->fields();

		$this->assertArrayHasKey('key', $fields);
		$this->assertArrayHasKey('value', $fields);

		$this->assertTrue($fields['key']['disabled']);
		$this->assertSame('textarea', $fields['value']['type']);
		$this->assertTrue($fields['value']['autofocus']);
	}

	public function testFieldsVariable(): void
	{
		$variable = $this->app->language('en')->variable('variable');
		$dialog   = new LanguageTranslationUpdateDialog($variable);
		$fields   = $dialog->fields();

		$this->assertSame('info', $fields['value']['type']);
	}

	public function testFor(): void
	{
		// key will be encoded in URL
		$key    = base64_encode(rawurlencode('normal'));
		$dialog = LanguageTranslationUpdateDialog::for('en', $key);
		$this->assertInstanceOf(LanguageTranslationUpdateDialog::class, $dialog);
		$this->assertSame('test', $dialog->variable()->value());
	}

	public function testNonExistentVariable(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.language.variable.notFound');

		$variable = $this->app->language('en')->variable('missing');
		new LanguageTranslationUpdateDialog($variable);
	}

	public function testIsVariableArray(): void
	{
		$variable = $this->app->language('en')->variable('normal');
		$dialog   = new LanguageTranslationUpdateDialog($variable);
		$this->assertFalse($dialog->isVariableArray());

		$variable = $this->app->language('en')->variable('variable');
		$dialog   = new LanguageTranslationUpdateDialog($variable);
		$this->assertTrue($dialog->isVariableArray());
	}

	public function testProps(): void
	{
		$variable = $this->app->language('en')->variable('normal');
		$dialog   = new LanguageTranslationUpdateDialog($variable);
		$props   = $dialog->props();
		$this->assertTrue($props['cancelButton']);
		$this->assertTrue($props['submitButton']);
		$this->assertSame([
			'key'   => 'normal',
			'value' => 'test'
		], $props['value']);
	}

	public function testPropsVariable(): void
	{
		$variable = $this->app->language('en')->variable('variable');
		$dialog   = new LanguageTranslationUpdateDialog($variable);
		$props   = $dialog->props();
		$this->assertFalse($props['cancelButton']);
		$this->assertFalse($props['submitButton']);
		$this->assertSame([
			'key'   => 'variable',
			'value' => ['one', 'two', 'three']
		], $props['value']);
	}

	public function testRender(): void
	{
		$variable = $this->app->language('en')->variable('normal');
		$dialog   = new LanguageTranslationUpdateDialog($variable);
		$result   = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query'  => [
					'value' => 'new'
				]
			]
		]);

		$variable = $this->app->language('en')->variable('normal');
		$dialog   = new LanguageTranslationUpdateDialog($variable);
		$this->assertSame('test', $dialog->variable()->value());

		$result = $dialog->submit();
		$this->assertTrue($result);
		$this->assertSame('new', $dialog->variable()->value());
	}
}
