<?php

namespace Kirby\Panel\Areas;

class UserDropdownsTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->installEditor();
		$this->login();
	}

	public function testUserDropdown(): void
	{
		$options = $this->dropdown('users/editor')['options'];

		$name = $options[0];

		$this->assertSame('/users/editor/changeName', $name['dialog']);
		$this->assertSame('Rename this user', $name['text']);

		$this->assertSame('-', $options[1]);

		$email = $options[2];

		$this->assertSame('/users/editor/changeEmail', $email['dialog']);
		$this->assertSame('Change email', $email['text']);

		$role = $options[3];

		$this->assertSame('/users/editor/changeRole', $role['dialog']);
		$this->assertSame('Change role', $role['text']);

		$language = $options[4];

		$this->assertSame('/users/editor/changeLanguage', $language['dialog']);
		$this->assertSame('Change language', $language['text']);

		$this->assertSame('-', $options[5]);

		$password = $options[6];

		$this->assertSame('/users/editor/changePassword', $password['dialog']);
		$this->assertSame('Change password', $password['text']);

		$this->assertSame('-', $options[7]);

		$delete = $options[8];

		$this->assertSame('/users/editor/delete', $delete['dialog']);
		$this->assertSame('Delete this user', $delete['text']);
	}

	public function testUserLanguageDropdown()
	{
		$this->app([
			'languages' => [
				'en' => [
					'code' => 'en',
					'name' => 'English',
				],
				'de' => [
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);

		$this->login();
		$this->assertLanguageDropdown('users/editor/languages');
	}
}
