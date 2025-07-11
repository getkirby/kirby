<?php

namespace Kirby\Panel\Areas;

class AccountDropdownsTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	public function testAccountDropdown(): void
	{
		$options = $this->dropdown('account')['options'];

		$name = $options[0];

		$this->assertSame('/account/changeName', $name['dialog']);
		$this->assertSame('Change your name', $name['text']);

		$this->assertSame('-', $options[1]);

		$email = $options[2];

		$this->assertSame('/account/changeEmail', $email['dialog']);
		$this->assertSame('Change email', $email['text']);

		$role = $options[3];

		$this->assertSame('/account/changeRole', $role['dialog']);
		$this->assertSame('Change role', $role['text']);


		$language = $options[4];

		$this->assertSame('/account/changeLanguage', $language['dialog']);
		$this->assertSame('Change language', $language['text']);

		$this->assertSame('-', $options[5]);

		$password = $options[6];

		$this->assertSame('/account/changePassword', $password['dialog']);
		$this->assertSame('Change password', $password['text']);

		$this->assertSame('-', $options[7]);

		$delete = $options[8];

		$this->assertSame('/account/delete', $delete['dialog']);
		$this->assertSame('Delete your account', $delete['text']);
	}

	public function testAccountLanguageDropdown()
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
		$this->assertLanguageDropdown('account/languages');
	}
}
