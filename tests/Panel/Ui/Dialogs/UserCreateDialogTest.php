<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserCreateDialog::class)]
class UserCreateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.UserCreateDialog';

	public function testProps(): void
	{
		$dialog = new UserCreateDialog();
		$props  = $dialog->props();
		$this->assertArrayHasKey('name', $props['fields']);
		$this->assertArrayHasKey('email', $props['fields']);
		$this->assertArrayHasKey('password', $props['fields']);
		$this->assertArrayHasKey('translation', $props['fields']);
		$this->assertArrayHasKey('role', $props['fields']);

		$this->assertSame('Create', $props['submitButton']);
		$this->assertSame([
			'name'        => '',
			'email'       => '',
			'password'    => '',
			'translation' => 'en',
			'role'        => 'admin'
		], $props['value']);
	}

	public function testPropsWithRoleQuery(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'title' => 'Editor',
				]
			],
			'request' => [
				'query' => [
					'role' => 'editor'
				]
			]
		]);

		$dialog = new UserCreateDialog();
		$props  = $dialog->props();
		$this->assertSame('editor', $props['value']['role']);
	}

	public function testRender(): void
	{
		$dialog = new UserCreateDialog();
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'email' => 'editor@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$dialog = new UserCreateDialog();
		$this->assertCount(1, $this->app->users());

		$result = $dialog->submit();
		$this->assertCount(2, $this->app->users());
		$this->assertSame('admin', $this->app->user('editor@getkirby.com')->role()->id());
		$this->assertSame('user.create', $result['event']);
	}
}
