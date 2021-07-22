<?php

namespace Kirby\Panel\Areas;

class UserDropdownsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    public function testUserDropdown(): void
    {
        $options = $this->dropdown('users/test')['options'];

        $name = $options[0];

        $this->assertSame('/users/test/changeName', $name['dialog']);
        $this->assertSame('Rename this user', $name['text']);

        $email = $options[1];

        $this->assertSame('/users/test/changeEmail', $email['dialog']);
        $this->assertSame('Change email', $email['text']);

        $role = $options[2];

        $this->assertSame('/users/test/changeRole', $role['dialog']);
        $this->assertSame('Change role', $role['text']);

        $password = $options[3];

        $this->assertSame('/users/test/changePassword', $password['dialog']);
        $this->assertSame('Change password', $password['text']);

        $language = $options[4];

        $this->assertSame('/users/test/changeLanguage', $language['dialog']);
        $this->assertSame('Change language', $language['text']);

        $delete = $options[5];

        $this->assertSame('/users/test/delete', $delete['dialog']);
        $this->assertSame('Delete this user', $delete['text']);
    }
}
