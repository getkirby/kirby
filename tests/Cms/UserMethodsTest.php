<?php

namespace Kirby\Cms;

class UserMethodsTest extends TestCase
{

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

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultLanguageWithoutStore()
    {
        $user = new User(['id' => 'user@domain.com']);
        $this->assertEquals('en', $user->language());
    }

    public function testDefaultLanguageWithStore()
    {
        $user = new User([
            'id'    => 'user@domain.com',
            'store' => new Store([
                'user.content' => function ($user) {
                    return new Content([], $user);
                }
            ])
        ]);

        $this->assertEquals('en', $user->language());
    }

    public function testDefaultLanguageWithContent()
    {
        $user = new User([
            'id'      => 'user@domain.com',
            'content' => new Content()
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

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultRoleWithoutStore()
    {
        $user = new User(['id' => 'user@domain.com']);
        $this->assertEquals('visitor', $user->role());
    }

    public function testDefaultRoleWithStore()
    {
        $user = new User([
            'id'    => 'user@domain.com',
            'store' => new Store([
                'user.content' => function ($user) {
                    return new Content([], $user);
                }
            ])
        ]);

        $this->assertEquals('visitor', $user->role());
    }

    public function testDefaultRoleWithContent()
    {
        $user = new User([
            'id'      => 'user@domain.com',
            'content' => new Content()
        ]);

        $this->assertEquals('visitor', $user->role());
    }

}
