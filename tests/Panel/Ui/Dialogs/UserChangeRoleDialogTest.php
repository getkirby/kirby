<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserChangeRoleDialog::class)]
class UserChangeRoleDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.UserChangeRoleDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'title' => 'Editor',
				]
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
				],
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor',
				]
			]
		]);
	}

	public function testFor(): void
	{
		$dialog = UserChangeRoleDialog::for('editor');
		$this->assertInstanceOf(UserChangeRoleDialog::class, $dialog);
		$this->assertSame($this->app->user('editor'), $dialog->user());
	}

	public function testProps(): void
	{
		$dialog = UserChangeRoleDialog::for('editor');
		$props  = $dialog->props();
		$this->assertArrayHasKey('role', $props['fields']);
		$this->assertSame('Change role', $props['submitButton']);
		$this->assertSame('editor', $props['value']['role']);
	}

	public function testRender(): void
	{
		$dialog = UserChangeRoleDialog::for('editor');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'role' => 'admin',
				]
			]
		]);

		$dialog = UserChangeRoleDialog::for('editor');
		$this->assertSame('editor', $dialog->user()->role()->id());

		$result = $dialog->submit();
		$this->assertSame('admin', $dialog->user()->role()->id());
		$this->assertSame('user.changeRole', $result['event']);
	}
}
