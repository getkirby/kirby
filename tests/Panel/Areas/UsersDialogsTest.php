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

    public function testChangeLanguage(): void
    {
        $dialog = $this->dialog('users/test/changeLanguage');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Language', $props['fields']['language']['label']);
        $this->assertSame('Change', $props['submitButton']);
        $this->assertSame('en', $props['value']['language']);
    }

    public function testChangeLanguageOnSubmit(): void
    {
        $this->submit([
            'language' => 'de'
        ]);

        $dialog = $this->dialog('users/test/changeLanguage');

        $this->assertSame('user.changeLanguage', $dialog['event']);
        $this->assertSame(['only' => '$translation'], $dialog['reload']);
        $this->assertSame(200, $dialog['code']);

        $this->assertSame('de', $this->app->user('test')->language());
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

    public function testChangePassword(): void
    {
        $dialog = $this->dialog('users/test/changePassword');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('New password', $props['fields']['password']['label']);
        $this->assertSame('Confirm the new password…', $props['fields']['passwordConfirmation']['label']);
        $this->assertSame('Change', $props['submitButton']);
    }

    public function testChangePasswordOnSubmit(): void
    {
        $this->submit([
            'password'             => '12345678',
            'passwordConfirmation' => '12345678'
        ]);

        $dialog = $this->dialog('users/test/changePassword');

        $this->assertSame('user.changePassword', $dialog['event']);
        $this->assertSame(200, $dialog['code']);

        $this->assertTrue($this->app->user('test')->validatePassword(12345678));
    }

    public function testChangePasswordOnSubmitWithInvalidPassword(): void
    {
        $this->submit([
            'password'             => '1234567',
            'passwordConfirmation' => '1234567'
        ]);

        $dialog = $this->dialog('users/test/changePassword');

        $this->assertSame(400, $dialog['code']);
        $this->assertSame('Please enter a valid password. Passwords must be at least 8 characters long.', $dialog['error']);
    }

    public function testChangePasswordOnSubmitWithInvalidConfirmation(): void
    {
        $this->submit([
            'password'             => '12345678',
            'passwordConfirmation' => '1234567'
        ]);

        $dialog = $this->dialog('users/test/changePassword');

        $this->assertSame(400, $dialog['code']);
        $this->assertSame('The passwords do not match', $dialog['error']);
    }

    public function testDelete(): void
    {
        // create a second user to be deleted
        $this->app->users()->create([
            'id'    => 'editor',
            'email' => 'editor@getkirby.com',
            'role'  => 'editor'
        ]);

        $dialog = $this->dialog('users/editor/delete');
        $props  = $dialog['props'];

        $this->assertRemoveDialog($dialog);
        $this->assertSame('Do you really want to delete <br><strong>editor@getkirby.com</strong>?', $props['text']);
    }

    public function testDeleteOnSubmit(): void
    {
        $this->submit([]);

        // create a second user to be deleted
        $this->app->users()->create([
            'id'    => 'editor',
            'email' => 'editor@getkirby.com',
            'role'  => 'editor'
        ]);

        $this->assertCount(2, $this->app->users());

        $dialog = $this->dialog('users/editor/delete');

        $this->assertSame('user.delete', $dialog['event']);
        $this->assertSame(['users/editor'], $dialog['dispatch']['content/remove']);
        $this->assertSame(200, $dialog['code']);
        $this->assertCount(1, $this->app->users());
    }
}
