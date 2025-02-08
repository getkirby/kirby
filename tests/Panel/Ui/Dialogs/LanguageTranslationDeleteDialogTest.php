<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Exception\NotFoundException;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageTranslationDeleteDialog::class)]
class LanguageTranslationDeleteDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.LanguageTranslationDeleteDialog';

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
						'foo' => 'bar'
					]
				]
			],
		]);
	}

	public function testFor(): void
	{
		// key will be encoded in URL
		$key    = base64_encode(rawurlencode('foo'));
		$dialog = LanguageTranslationDeleteDialog::for('en', $key);
		$this->assertInstanceOf(LanguageTranslationDeleteDialog::class, $dialog);
		$this->assertSame('bar', $dialog->variable()->value());
	}

	public function testNonExistentVariable(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.language.variable.notFound');

		$variable = $this->app->language('en')->variable('missing');
		new LanguageTranslationDeleteDialog($variable);
	}

	public function testProps(): void
	{
		$variable = $this->app->language('en')->variable('foo');
		$dialog   = new LanguageTranslationDeleteDialog($variable);
		$props    = $dialog->props();
		$this->assertSame('Do you really want to delete the variable for foo?', $props['text']);
	}

	public function testRender(): void
	{
		$variable = $this->app->language('en')->variable('foo');
		$dialog   = new LanguageTranslationDeleteDialog($variable);
		$result   = $dialog->render();
		$this->assertSame('k-remove-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->assertCount(1, $this->app->language('en')->translations());

		$variable = $this->app->language('en')->variable('foo');
		$dialog   = new LanguageTranslationDeleteDialog($variable);
		$result   = $dialog->submit();

		$this->assertTrue($result);
		$this->assertCount(0, $this->app->language('en')->translations());
	}
}
