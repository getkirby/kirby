<?php

namespace Kirby\Cms;

class UserMethodsTest extends TestCase
{
    public function setUp(): void
    {
        // make sure field methods are loaded
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testId()
    {
        $user = new User([
            'id'    => 'test',
            'email' => 'user@domain.com'
        ]);
        $this->assertEquals('test', $user->id());
    }

    public function testLanguage()
    {
        $user = new User([
            'email'    => 'user@domain.com',
            'language' => 'en',
        ]);

        $this->assertEquals('en', $user->language());
    }

    public function testDefaultLanguage()
    {
        $user = new User([
            'email' => 'user@domain.com',
        ]);

        $this->assertEquals('en', $user->language());
    }

    public function testRole()
    {
        $kirby = new App([
            'roles' => [
                ['name' => 'editor', 'title' => 'Editor']
            ]
        ]);

        $user = new User([
            'email' => 'user@domain.com',
            'role'  => 'editor',
            'kirby' => $kirby
        ]);

        $this->assertEquals('editor', $user->role()->name());
    }

    public function testDefaultRole()
    {
        $user = new User([
            'email' => 'user@domain.com',
        ]);

        $this->assertEquals('nobody', $user->role()->name());
    }
}
