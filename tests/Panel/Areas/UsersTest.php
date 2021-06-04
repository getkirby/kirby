<?php

namespace Kirby\Panel\Areas;

class UsersTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
    }

    public function testUsersWithoutAuthentication(): void
    {
        $this->assertRedirect('users', 'login');
    }

    public function testUsers(): void
    {
        $this->login();

        $view  = $this->view('users');
        $props = $view['props'];

        $this->assertSame('users', $view['id']);
        $this->assertSame('k-users-view', $view['component']);
        $this->assertSame('Users', $view['title']);

        // TODO: add more props tests
    }

    public function testUser(): void
    {
        $this->login();

        $view  = $this->view('users/test');
        $props = $view['props'];

        $this->assertSame('users', $view['id']);
        $this->assertSame('k-user-view', $view['component']);
        $this->assertSame('test@getkirby.com', $view['title']);
    }

    public function testUserWithMissingModel(): void
    {
        $this->login();
        $this->assertErrorView('users/does-not-exist', 'The user "does-not-exist" cannot be found');
    }
}
