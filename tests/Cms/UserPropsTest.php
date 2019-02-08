<?php

namespace Kirby\Cms;

use Kirby\Toolkit\F;

class UserPropsTest extends TestCase
{
    public function testAvatar()
    {
        $user = new User([
            'email' => 'user@domain.com'
        ]);

        $this->assertNull($user->avatar());
    }

    public function testDefaultSiblings()
    {
        $user = new User(['email' => 'user@domain.com']);
        $this->assertInstanceOf(Users::class, $user->siblings());
    }

    public function testContent()
    {
        $user = new User([
            'email'   => 'user@domain.com',
            'content' => $content = ['name' => 'Test']
        ]);

        $this->assertEquals($content, $user->content()->toArray());
    }

    public function testInvalidContent()
    {
        $this->expectException('TypeError');

        $user = new User(['email' => 'user@domain.com', 'content' => 'something']);
    }

    public function testDefaultContent()
    {
        $user = new User(['email' => 'user@domain.com']);
        $this->assertInstanceOf(Content::class, $user->content());
    }

    public function testEmail()
    {
        $user = new User([
            'email' => $email = 'user@domain.com',
        ]);

        $this->assertEquals($email, $user->email());
    }

    public function testInvalidEmail()
    {
        $this->expectException('TypeError');

        $user = new User(['email' => []]);
    }

    public function testToString()
    {
        $user = new User(['email' => 'user@domain.com']);
        $this->assertEquals('user@domain.com', $user->toString('{{ user.email }}'));
    }

    public function testModified()
    {
        $app = new App([
            'roots' => [
                'index'    => $index = __DIR__ . '/fixtures/UserPropsTest/modified',
                'accounts' => $index
            ]
        ]);

        // create a user file
        F::write($file = $index . '/test/index.php', 'test');

        $modified = filemtime($file);
        $user     = $app->user('test');

        $this->assertEquals($modified, $user->modified());

        // default date handler
        $format = 'd.m.Y';
        $this->assertEquals(date($format, $modified), $user->modified($format));

        // custom date handler
        $format = '%d.%m.%Y';
        $this->assertEquals(strftime($format, $modified), $user->modified($format, 'strftime'));

        Dir::remove($index);
    }
}
