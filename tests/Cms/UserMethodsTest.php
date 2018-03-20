<?php

namespace Kirby\Cms;

class UserMethodsTest extends TestCase
{

    public function setUp()
    {
        // make sure field methods are loaded
        new App();
    }

    public function testId()
    {
        $user = new User(['email' => $id = 'user@domain.com']);
        $this->assertEquals(sha1($id), $user->id());
    }

    public function testLanguage()
    {
        $user = new User([
            'email'    => 'user@domain.com',
            'language' => 'en_US',
        ]);

        $this->assertEquals('en_US', $user->language());
    }

    public function testDefaultLanguage()
    {
        $user = new User([
            'email' => 'user@domain.com',
        ]);

        $this->assertEquals('en_US', $user->language());
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
