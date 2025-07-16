<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserDeleteDialog::class)]
class UserDeleteDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.UserDeleteDialog';

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
		$dialog = UserDeleteDialog::for('editor');
		$this->assertInstanceOf(UserDeleteDialog::class, $dialog);
		$this->assertSame($this->app->user('editor'), $dialog->user());
	}

	public function testProps(): void
	{
		$dialog = UserDeleteDialog::for('editor');
		$props  = $dialog->props();
		$this->assertSame('Do you really want to delete <br><strong>editor@getkirby.com</strong>?', $props['text']);
	}

	public function testRender(): void
	{
		$dialog = UserDeleteDialog::for('editor');
		$result = $dialog->render();
		$this->assertSame('k-remove-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_referrer' => 'users/editor'
				]
			]
		]);

		$dialog = UserDeleteDialog::for('editor');
		$this->assertCount(2, $this->app->users());

		$result = $dialog->submit();
		$this->assertCount(1, $this->app->users());
		$this->assertSame('user.delete', $result['event']);
		$this->assertSame('/users', $result['redirect']);
	}

	public function testSubmitCurrentUser(): void
	{
		$this->app = $this->app->clone([
			'user' => 'editor'
		]);

		$dialog = UserDeleteDialog::for('editor');
		$result = $dialog->submit();
		$this->assertSame('/logout', $result['redirect']);
	}
}
