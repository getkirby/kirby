<?php

namespace Kirby\Cms;

class UserMethodsTest extends TestCase
{

    public function setUp()
    {
        // make sure field methods are loaded
        new App();
    }

    public function testHash()
    {
        $user = new User(['id' => $id = 'user@domain.com']);
        $this->assertEquals(sha1($id), $user->hash());
    }

    public function testLanguage()
    {
        $user = new User([
            'id' => 'user@domain.com',
            'content' => new Content([
                'language' => 'de'
            ])
        ]);

        $this->assertEquals('de', $user->language());
    }

    public function testDefaultLanguage()
    {
        $user = new User([
            'id' => 'user@domain.com',
        ]);

        $this->assertEquals('en', $user->language());
    }

    public function testRole()
    {
        $user = new User([
            'id' => 'user@domain.com',
            'content' => new Content([
                'role' => 'editor'
            ])
        ]);

        $this->assertEquals('editor', $user->role());
    }

    public function testDefaultRole()
    {
        $user = new User([
            'id' => 'user@domain.com',
        ]);

        $this->assertEquals('visitor', $user->role());
    }

}
