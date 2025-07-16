<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserChangeLanguageDialog::class)]
class UserChangeLanguageDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.UserChangeLanguageDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'languages' => [
				[
					'code' => 'en',
					'name' => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);
	}

	public function testFor(): void
	{
		$dialog = UserChangeLanguageDialog::for('test');
		$this->assertInstanceOf(UserChangeLanguageDialog::class, $dialog);
		$this->assertSame($this->app->user('test'), $dialog->user());
	}

	public function testProps(): void
	{
		$dialog = UserChangeLanguageDialog::for('test');
		$props  = $dialog->props();
		$this->assertArrayHasKey('translation', $props['fields']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame('en', $props['value']['translation']);
	}

	public function testRender(): void
	{
		$dialog = UserChangeLanguageDialog::for('test');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'translation' => 'de',
				]
			]
		]);

		$dialog = UserChangeLanguageDialog::for('test');
		$this->assertSame('en', $dialog->user()->language());

		$result = $dialog->submit();
		$this->assertSame('de', $dialog->user()->language());
		$this->assertSame('user.changeLanguage', $result['event']);
		$this->assertSame('$translation', $result['reload']['globals']);
	}
}
