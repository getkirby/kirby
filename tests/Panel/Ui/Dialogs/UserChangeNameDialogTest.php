<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserChangeNameDialog::class)]
class UserChangeNameDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.UserChangeNameDialog';

	public function testFor(): void
	{
		$dialog = UserChangeNameDialog::for('test');
		$this->assertInstanceOf(UserChangeNameDialog::class, $dialog);
		$this->assertSame($this->app->user('test'), $dialog->user());
	}

	public function testProps(): void
	{
		$dialog = UserChangeNameDialog::for('test');
		$props  = $dialog->props();
		$this->assertArrayHasKey('name', $props['fields']);
		$this->assertSame('Rename', $props['submitButton']);
		$this->assertNull($props['value']['name']);
	}

	public function testRender(): void
	{
		$dialog = UserChangeNameDialog::for('test');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'name' => 'Homer Simpson',
				]
			]
		]);

		$dialog = UserChangeNameDialog::for('test');
		$this->assertNull($dialog->user()->name()->value());

		$result = $dialog->submit();
		$this->assertSame('Homer Simpson', $dialog->user()->name()->value());
		$this->assertSame('user.changeName', $result['event']);
	}
}
