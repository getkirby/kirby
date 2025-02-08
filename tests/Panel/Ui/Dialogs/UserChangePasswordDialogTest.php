<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserChangePasswordDialog::class)]
class UserChangePasswordDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.UserChangePasswordDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'       => 'test',
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => User::hashPassword('12345678')
				]
			],
		]);
	}

	public function testFor(): void
	{
		$dialog = UserChangePasswordDialog::for('test');
		$this->assertInstanceOf(UserChangePasswordDialog::class, $dialog);
		$this->assertSame($this->app->user('test'), $dialog->user());
	}

	public function testProps(): void
	{
		$dialog = UserChangePasswordDialog::for('test');
		$props  = $dialog->props();
		$this->assertArrayHasKey('currentPassword', $props['fields']);
		$this->assertArrayHasKey('password', $props['fields']);
		$this->assertArrayHasKey('passwordConfirmation', $props['fields']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame([], $props['value']);
	}

	public function testRender(): void
	{
		$dialog = UserChangePasswordDialog::for('test');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'currentPassword'      => '12345678',
					'password'             => 'abcdefgh',
					'passwordConfirmation' => 'abcdefgh'
				]
			]
		]);

		$dialog = UserChangePasswordDialog::for('test');
		$this->assertTrue($dialog->user()->validatePassword('12345678'));

		$result = $dialog->submit();
		$this->assertTrue($dialog->user()->validatePassword('abcdefgh'));
		$this->assertSame('user.changePassword', $result['event']);
	}

	public function testSubmitInvalidCurrentPassword(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'currentPassword'      => '123456',
					'password'             => 'abcdefgh',
					'passwordConfirmation' => 'abcdefgh'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Wrong password');

		$dialog = UserChangePasswordDialog::for('test');
		$dialog->submit();
	}

	public function testSubmitInvalidNewPassword(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'currentPassword'      => '12345678',
					'password'             => 'abcdef',
					'passwordConfirmation' => 'abcdef'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid password. Passwords must be at least 8 characters long.');

		$dialog = UserChangePasswordDialog::for('test');
		$dialog->submit();
	}

	public function testSubmitInvalidPasswordConfirmation(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'currentPassword'      => '12345678',
					'password'             => 'abcdefgh',
					'passwordConfirmation' => 'abcdefg'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passwords do not match');

		$dialog = UserChangePasswordDialog::for('test');
		$dialog->submit();
	}
}
