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

    public function testCreate(): void
    {
        $dialog = $this->dialog('users/create');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        // check for all fields
        $this->assertSame('Name', $props['fields']['name']['label']);
        $this->assertSame('Email', $props['fields']['email']['label']);
        $this->assertSame('Password', $props['fields']['password']['label']);
        $this->assertSame('Language', $props['fields']['translation']['label']);
        $this->assertSame('Role', $props['fields']['role']['label']);

        $this->assertSame('Create', $props['submitButton']);

        // check values
        $this->assertSame('', $props['value']['name']);
        $this->assertSame('', $props['value']['email']);
        $this->assertSame('', $props['value']['password']);
        $this->assertSame('en', $props['value']['translation']);
        $this->assertSame('admin', $props['value']['role']);
    }

    public function testCreateOnSubmit(): void
    {
        $this->submit([
            'name'  => 'Peter',
            'email' => 'test2@getkirby.com',
            'role'  => 'admin'
        ]);

        $dialog = $this->dialog('users/create');

        $this->assertSame('user.create', $dialog['event']);
        $this->assertSame(200, $dialog['code']);

        $user = $this->app->user('test2@getkirby.com');

        $this->assertSame('Peter', $user->name()->value());
        $this->assertSame('admin', $user->role()->name());
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
        $this->assertSame(200, $dialog['code']);

        $this->assertSame('test2@getkirby.com', $this->app->user('test')->email());
    }

    public function testChangeLanguage(): void
    {
        $dialog = $this->dialog('users/test/changeLanguage');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Language', $props['fields']['translation']['label']);
        $this->assertSame('Change', $props['submitButton']);
        $this->assertSame('en', $props['value']['translation']);
    }

    public function testChangeLanguageOnSubmit(): void
    {
        $this->submit([
            'translation' => 'de'
        ]);

        $dialog = $this->dialog('users/test/changeLanguage');

        $this->assertSame('user.changeLanguage', $dialog['event']);
        $this->assertSame(['globals' => '$translation'], $dialog['reload']);
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

    public function testChangeRole(): void
    {
        $this->installEditor();
        $this->login();

        $dialog = $this->dialog('users/editor/changeRole');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Select a new role', $props['fields']['role']['label']);
        $this->assertSame('Change role', $props['submitButton']);
        $this->assertSame('editor', $props['value']['role']);
    }

    public function testChangeRoleOnSubmit(): void
    {
        $this->installEditor();
        $this->login();

        $this->submit([
            'role' => 'admin'
        ]);

        $dialog = $this->dialog('users/editor/changeRole');

        $this->assertSame('user.changeRole', $dialog['event']);
        $this->assertSame(200, $dialog['code']);
        $this->assertSame('admin', $this->app->user('editor')->role()->name());
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
        $this->assertSame(['/users/editor'], $dialog['dispatch']['content/remove']);
        $this->assertSame(200, $dialog['code']);
        $this->assertFalse($dialog['redirect']);
        $this->assertCount(1, $this->app->users());
    }

    public function testDeleteOnSubmitWithReferrer(): void
    {
        $this->submit([
            '_referrer' => '/users/editor'
        ]);

        // create a second user to be deleted
        $this->app->users()->create([
            'id'    => 'editor',
            'email' => 'editor@getkirby.com',
            'role'  => 'editor'
        ]);

        $dialog = $this->dialog('users/editor/delete');

        $this->assertSame('/users', $dialog['redirect']);
    }

    public function testDeleteOnSubmitWithOwnAccount(): void
    {
        $this->submit([
            '_referrer' => '/users/editor'
        ]);

        // create a second user to be deleted
        $this->app->users()->create([
            'id'    => 'editor',
            'email' => 'editor@getkirby.com',
            'role'  => 'admin'
        ]);

        // login as the secondary user
        $this->login('editor');

        $dialog = $this->dialog('users/editor/delete');

        $this->assertSame('/logout', $dialog['redirect']);
    }
}
