<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class UserTestModel extends User
{
}

class UserTestForceLocked extends User
{
    public function isLocked(): bool
    {
        return true;
    }
}

class UserTest extends TestCase
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

    public function testName()
    {
        $user = new User([
            'name' => $name = 'Homer Simpson',
        ]);

        $this->assertEquals($name, $user->name());
    }

    public function testNameSanitized()
    {
        $user = new User([
            'name' => '<strong>Homer</strong> Simpson',
        ]);

        $this->assertEquals('Homer Simpson', $user->name());
    }

    public function testNameOrEmail()
    {
        $user = new User([
            'email' => $email = 'homer@simpsons.com',
            'name'  => $name = 'Homer Simpson',
        ]);

        $this->assertSame($name, $user->nameOrEmail()->value());
        $this->assertSame('name', $user->nameOrEmail()->key());

        $user = new User([
            'email' => $email = 'homer@simpsons.com',
            'name'  => ''
        ]);

        $this->assertSame($email, $user->nameOrEmail()->value());
        $this->assertSame('email', $user->nameOrEmail()->key());
    }

    public function testToString()
    {
        $user = new User([
            'email' => 'test@getkirby.com'
        ]);

        $this->assertEquals('test@getkirby.com', $user->toString());
    }

    public function testToStringWithTemplate()
    {
        $user = new User([
            'email' => 'test@getkirby.com'
        ]);

        $this->assertEquals('Email: test@getkirby.com', $user->toString('Email: {{ user.email }}'));
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
        F::write($file = $index . '/test/index.php', '<?php return [];');

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

    public function testModifiedSpecifyingLanguage()
    {
        $app = new App([
            'roots' => [
                'index'    => $index = __DIR__ . '/fixtures/UserPropsTest/modified',
                'accounts' => $index
            ],
            'languages' => [
                [
                    'code'    => 'en',
                    'default' => true,
                    'name'    => 'English'
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch'
                ]
            ]
        ]);

        // create a user file
        F::write($file = $index . '/test/index.php', '<?php return [];');

        // create the english page
        F::write($file = $index . '/test/user.en.txt', 'test');
        touch($file, $modifiedEnContent = \time() + 2);

        // create the german page
        F::write($file = $index . '/test/user.de.txt', 'test');
        touch($file, $modifiedDeContent = \time() + 5);

        $user = $app->user('test');

        $this->assertEquals($modifiedEnContent, $user->modified('U', null, 'en'));
        $this->assertEquals($modifiedDeContent, $user->modified('U', null, 'de'));

        Dir::remove($index);
    }

    public function passwordProvider()
    {
        return [
            [null, false],
            ['', false],
            ['short', false],
            ['invalid-password', false],
            ['correct-horse-battery-staple', true],
        ];
    }

    /**
     * @dataProvider passwordProvider
     */
    public function testValidatePassword($input, $valid)
    {
        $user = new User([
            'email'    => 'test@getkirby.com',
            'password' => User::hashPassword('correct-horse-battery-staple')
        ]);

        if ($valid === false) {
            $this->expectException('Kirby\Exception\InvalidArgumentException');
            $user->validatePassword($input);
        } else {
            $this->assertTrue($user->validatePassword($input));
        }
    }

    public function testValidateUndefinedPassword()
    {
        $user = new User([
            'email' => 'test@getkirby.com',
        ]);

        $this->expectException('Kirby\Exception\NotFoundException');
        $user->validatePassword('test');
    }

    public function testIsAdmin()
    {
        $user = new User([
            'email' => 'test@getkirby.com',
            'role'  => 'admin'
        ]);

        $this->assertTrue($user->isAdmin());

        $user = new User([
            'email' => 'test@getkirby.com',
            'role'  => 'editor'
        ]);

        $this->assertFalse($user->isAdmin());
    }

    public function testIsLoggedIn()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                ['email' => 'a@getkirby.com'],
                ['email' => 'b@getkirby.com']
            ],
        ]);

        $a = $app->user('a@getkirby.com');
        $b = $app->user('b@getkirby.com');

        $this->assertFalse($a->isLoggedIn());
        $this->assertFalse($b->isLoggedIn());

        $app->impersonate('a@getkirby.com');

        $this->assertTrue($a->isLoggedIn());
        $this->assertFalse($b->isLoggedIn());

        $app->impersonate('b@getkirby.com');

        $this->assertFalse($a->isLoggedIn());
        $this->assertTrue($b->isLoggedIn());
    }

    public function testQuery()
    {
        $user = new User([
            'email' => 'test@getkirby.com',
            'name'  => 'Test User'
        ]);

        $this->assertEquals('Test User', $user->query('user.name'));
        $this->assertEquals('test@getkirby.com', $user->query('user.email'));
    }

    public function testUserMethods()
    {
        User::$methods = [
            'test' => function () {
                return 'homer';
            }
        ];

        $user = new User([
            'email' => 'test@getkirby.com',
            'name'  => 'Test User'
        ]);

        $this->assertEquals('homer', $user->test());

        User::$methods = [];
    }

    public function testUserModel()
    {
        User::$models = [
            'dummy' => UserTestModel::class
        ];

        $user = User::factory([
            'slug'  => 'test',
            'model' => 'dummy'
        ]);

        $this->assertInstanceOf(UserTestModel::class, $user);

        User::$models = [];
    }

    public function testPanelOptions()
    {
        $user = new User([
            'email' => 'test@getkirby.com',
        ]);

        $user->kirby()->impersonate('kirby');

        $expected = [
            'create'         => true,
            'changeEmail'    => true,
            'changeLanguage' => true,
            'changeName'     => true,
            'changePassword' => true,
            'changeRole'     => false, // just one role
            'delete'         => true,
            'update'         => true,
        ];

        $this->assertEquals($expected, $user->panelOptions());
    }

    public function testPanelOptionsWithLockedUser()
    {
        $user = new UserTestForceLocked([
            'email' => 'test@getkirby.com',
        ]);

        $user->kirby()->impersonate('kirby');

        // without override
        $expected = [
            'create'         => false,
            'changeEmail'    => false,
            'changeLanguage' => false,
            'changeName'     => false,
            'changePassword' => false,
            'changeRole'     => false,
            'delete'         => false,
            'update'         => false,
        ];

        $this->assertEquals($expected, $user->panelOptions());

        // with override
        $expected = [
            'create'         => false,
            'changeEmail'    => true,
            'changeLanguage' => false,
            'changeName'     => false,
            'changePassword' => false,
            'changeRole'     => false,
            'delete'         => false,
            'update'         => false,
        ];

        $this->assertEquals($expected, $user->panelOptions(['changeEmail']));
    }
}
