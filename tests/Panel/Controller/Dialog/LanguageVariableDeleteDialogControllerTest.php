<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\NotFoundException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageVariableDeleteDialogController::class)]
class LanguageVariableDeleteDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.LanguageVariableDeleteDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'         => 'en',
					'default'      => true,
					'name'         => 'English',
					'translations' => [
						'foo' => 'bar'
					]
				]
			],
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		// key will be encoded in URL
		$key    = base64_encode(rawurlencode('foo'));
		$controller = LanguageVariableDeleteDialogController::factory('en', $key);
		$this->assertInstanceOf(LanguageVariableDeleteDialogController::class, $controller);
		$this->assertSame('bar', $controller->variable->value());
	}

	public function testFactoryNonExistentVariable(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.language.variable.notFound');

		$variable = $this->app->language('en')->variable('missing');
		LanguageVariableDeleteDialogController::factory('en', $variable->key());
	}

	public function testLoad(): void
	{
		$variable   = $this->app->language('en')->variable('foo');
		$controller = new LanguageVariableDeleteDialogController($variable);
		$dialog     = $controller->load();
		$this->assertInstanceOf(RemoveDialog::class, $dialog);
		$this->assertSame('k-remove-dialog', $dialog->component);

		$props = $dialog->props();
		$this->assertSame('Do you really want to delete the variable for foo?', $props['text']);
	}

	public function testSubmit(): void
	{
		$this->assertCount(1, $this->app->language('en')->translations());

		$variable   = $this->app->language('en')->variable('foo');
		$controller = new LanguageVariableDeleteDialogController($variable);
		$response   = $controller->submit();

		$this->assertTrue($response);
		$this->assertCount(0, $this->app->language('en')->translations());
	}
}
