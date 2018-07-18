<?php

namespace Kirby\Cms;

class UserActionsTest extends TestCase
{

    protected $app;
    protected $fixtures;

    public function setUp()
    {
        $this->app = new App([
            'roles' => [
                [
                    'name' => 'admin'
                ],
                [
                    'name' => 'editor'
                ]
            ],
            'roots' => [
                'index'    => '/dev/null',
                'accounts' => $this->fixtures = __DIR__ . '/fixtures/UserActionsTest',
            ],
            'user'  => 'admin@domain.com',
            'users' => [
                [
                    'email' => 'admin@domain.com',
                    'role'  => 'admin'
                ],
                [
                    'email' => 'editor@domain.com',
                    'role'  => 'editor'
                ]
            ],
        ]);

        Dir::remove($this->fixtures);

    }

    public function tearDown()
    {
        Dir::remove($this->fixtures);
    }

    public function testChangeEmail()
    {
        $user = $this->app->user('editor@domain.com');
        $user = $user->changeEmail('another@domain.com');

        $this->assertEquals('another@domain.com', $user->email());
    }

    public function testChangeLanguage()
    {
        $user = $this->app->user('editor@domain.com');
        $user = $user->changeLanguage('de');

        $this->assertEquals('de', $user->language());
    }

    public function testChangeName()
    {
        $user = $this->app->user('editor@domain.com');
        $user = $user->changeName('Edith Thor');

        $this->assertEquals('Edith Thor', $user->name());
    }

    public function testChangePassword()
    {
        $user = $this->app->user('editor@domain.com');
        $user = $user->changePassword('topsecret2018');

        $this->assertTrue($user->validatePassword('topsecret2018'));
    }

    public function testChangeRole()
    {
        $user = $this->app->user('editor@domain.com');
        $user = $user->changeRole('editor');

        $this->assertEquals('editor', $user->role());
    }

    public function testCreate()
    {
        $user = User::create([
            'email' => 'new@domain.com',
            'role'  => 'editor',
        ]);

        $this->assertTrue($user->exists());

        $this->assertEquals('new@domain.com', $user->email());
        $this->assertEquals('editor', $user->role());
    }

    public function testDelete()
    {
        $user = $this->app->user('editor@domain.com');
        $user->save();

        $this->assertFileExists($user->root() . '/user.txt');
        $user->delete();
        $this->assertFileNotExists($user->root() . '/user.txt');
    }

    public function testUpdate()
    {
        $user = $this->app->user('editor@domain.com');
        $user->update([
            'website' => $url = 'https://editor.com'
        ]);

        $this->assertEquals($url, $user->website()->value());
    }

}
