<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserChangeEmailDialog::class)]
class UserChangeEmailDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.UserChangeEmailDialog';


	public function testFor(): void
	{
		$dialog = UserChangeEmailDialog::for('test');
		$this->assertInstanceOf(UserChangeEmailDialog::class, $dialog);
		$this->assertSame($this->app->user('test'), $dialog->user());
	}

	public function testProps(): void
	{
		$dialog = UserChangeEmailDialog::for('test');
		$props  = $dialog->props();
		$this->assertArrayHasKey('email', $props['fields']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame('test@getkirby.com', $props['value']['email']);
	}

	public function testRender(): void
	{
		$dialog = UserChangeEmailDialog::for('test');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'email' => 'foo@getkirby.com',
				]
			]
		]);

		$dialog = UserChangeEmailDialog::for('test');
		$this->assertSame('test@getkirby.com', $dialog->user()->email());

		$result = $dialog->submit();
		$this->assertSame('foo@getkirby.com', $dialog->user()->email());
		$this->assertSame('user.changeEmail', $result['event']);
	}
}
