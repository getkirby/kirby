<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Language;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageVariableFormDialogController::class)]
class LanguageVariableFormDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.LanguageVariableFormDialogController';

	protected Language $language;

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English',
					'translations' => [
						'normal'   => 'test',
						'variable' => ['one', 'two', 'three']
					]
				],
				'de' => [
					'code' => 'de',
					'name' => 'Deutsch'
				]
			],
		]);

		$this->language = $this->app->language('en');

		$this->app->impersonate('kirby');
	}
	public function testFactoryForCreate(): void
	{
		$controller = LanguageVariableFormDialogController::factory('en');
		$this->assertInstanceOf(LanguageVariableFormDialogController::class, $controller);
		$this->assertSame($this->language, $controller->language);
	}

	public function testFactoryForUpdate(): void
	{
		$key        = base64_encode(rawurlencode('normal'));
		$controller = LanguageVariableFormDialogController::factory('en', $key);
		$this->assertInstanceOf(LanguageVariableFormDialogController::class, $controller);
		$this->assertSame('test', $controller->variable->value());
	}

	public function testFactoryForUpdateNonExistentVariable(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.language.variable.notFound');

		$key = base64_encode(rawurlencode('missing'));
		LanguageVariableFormDialogController::factory('en', $key);
	}

	public function testFieldsForCreate(): void
	{
		$controller = new LanguageVariableFormDialogController($this->language);
		$fields     = $controller->fields();

		$this->assertArrayHasKey('key', $fields);
		$this->assertArrayHasKey('value', $fields);

		$this->assertSame('text', $fields['key']['type']);
		$this->assertSame('textarea', $fields['value']['type']);
	}

	public function testFieldsForUpdate(): void
	{
		$variable   = $this->language->variable('normal');
		$controller = new LanguageVariableFormDialogController($this->language, $variable);
		$fields     = $controller->fields();

		$this->assertArrayHasKey('key', $fields);
		$this->assertArrayHasKey('value', $fields);

		$this->assertTrue($fields['key']['disabled']);
		$this->assertSame('textarea', $fields['value']['type']);
		$this->assertTrue($fields['value']['autofocus']);
	}

	public function testFieldsForUpdateHasMultipleValues(): void
	{
		$variable   = $this->language->variable('variable');
		$controller = new LanguageVariableFormDialogController($this->language, $variable);
		$fields     = $controller->fields();

		$this->assertTrue($fields['entries']['autofocus']);
	}

	public function testHasMultipleValues(): void
	{
		$variable   = $this->language->variable('normal');
		$controller = new LanguageVariableFormDialogController($this->language, $variable);
		$this->assertFalse($controller->hasMultipleValues());

		$variable   = $this->language->variable('variable');
		$controller = new LanguageVariableFormDialogController($this->language, $variable);
		$this->assertTrue($controller->hasMultipleValues());
	}

	public function testLoadForCreate(): void
	{
		$controller = new LanguageVariableFormDialogController($this->language);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);
		$this->assertSame('k-form-dialog', $dialog->component);

		$props = $dialog->props();
		$this->assertSame('large', $props['size']);
		$this->assertCount(4, $props['fields']);
		$this->assertSame(['multiple' => false], $props['value']);
	}

	public function testLoadForUpdate(): void
	{
		$variable   = $this->language->variable('normal');
		$controller = new LanguageVariableFormDialogController($this->language, $variable);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame([
			'key'      => 'normal',
			'multiple' => false,
			'value'    => 'test',
		], $props['value']);
	}

	public function testLoadForUpdateHasMultipleValues(): void
	{
		$variable   = $this->language->variable('variable');
		$controller = new LanguageVariableFormDialogController($this->language, $variable);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame([
			'entries'  => ['one', 'two', 'three'],
			'key'      => 'variable',
			'multiple' => true,
		], $props['value']);
	}

	public function testSubmitForCreate(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query'  => [
					'key'   => 'foo',
					'value' => 'bar'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$this->assertNull($this->language->variable('foo')->value());

		$controller = new LanguageVariableFormDialogController($this->language);
		$result     = $controller->submit();

		$this->assertTrue($result);

		// retrieve new/updated language object
		$language = $this->app->language('en');

		$this->assertSame('bar', $language->variable('foo')->value());
	}

	public function testSubmitForCreateNonDefaultLanguage(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query'  => [
					'key'   => 'fö',
					'value' => 'bär'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$language = $this->app->language('de');
		$this->assertNull($language->variable('fö')->value());

		$controller = new LanguageVariableFormDialogController($language);
		$result     = $controller->submit();

		$this->assertTrue($result);

		// retrieve new/updated language object
		$language = $this->app->language('de');

		$this->assertSame('bär', $language->variable('fö')->value());
	}

	public function testSubmitForUpdate(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query'  => [
					'value' => 'new'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$variable   = $this->language->variable('normal');
		$controller = new LanguageVariableFormDialogController($this->language, $variable);
		$this->assertSame('test', $controller->variable->value());

		$result = $controller->submit();
		$this->assertTrue($result);
		$this->assertSame('new', $controller->variable->value());
	}
}
