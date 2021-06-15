<?php

namespace Kirby\Panel\Areas;

class UsersDialogsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    public function testChangeName(): void
    {
        $dialog = $this->dialog('users/test@getkirby.com/changeName');

        $this->assertFormDialog($dialog);
        $this->assertSame(['name' => 'test@getkirby.com'], $dialog['props']);
    }
}
