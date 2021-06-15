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

    public function testChangeEmail(): void
    {
        $dialog = $this->dialog('users/test/changeEmail');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Email', $props['fields']['email']['label']);
        $this->assertSame('Change', $props['submitButton']);
        $this->assertSame('test@getkirby.com', $props['value']['email']);
    }

    public function testChangeEmailOnSubmit(): void
    {
        $this->submit([
            'email' => 'test2@getkirby.com'
        ]);

        $dialog = $this->dialog('users/test/changeEmail');

        $this->assertSame('user.changeEmail', $dialog['event']);
        $this->assertSame(['users/test'], $dialog['dispatch']['content/revert']);
        $this->assertSame(200, $dialog['code']);

        $this->assertSame('test2@getkirby.com', $this->app->user('test')->email());
    }

    public function testChangeName(): void
    {
        $dialog = $this->dialog('users/test/changeName');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Name', $props['fields']['name']['label']);
        $this->assertSame('Rename', $props['submitButton']);
        $this->assertNull($props['value']['name']);
    }

    public function testChangeNameOnSubmit(): void
    {
        $this->submit([
            'name' => 'Peter'
        ]);

        $dialog = $this->dialog('users/test/changeName');

        $this->assertSame('user.changeName', $dialog['event']);
        $this->assertSame(200, $dialog['code']);

        $this->assertSame('Peter', $this->app->user('test')->username());
    }

}
