<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;

class UserActionsTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
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

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testChangeEmail()
    {
        $user = $this->app->user('editor@domain.com');
        $user = $user->changeEmail('another@domain.com');

        $this->assertEquals('another@domain.com', $user->email());
    }

    public function testChangeEmailWithUnicode()
    {
        $user = $this->app->user('editor@domain.com');

        // with Unicode email
        $user = $user->changeEmail('test@exämple.com');
        $this->assertSame('test@exämple.com', $user->email());

        // with Punycode email
        $user = $user->changeEmail('test@xn--tst-qla.com');
        $this->assertSame('test@täst.com', $user->email());
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

    public function testCreateAdmin()
    {
        $user = User::create([
            'email' => 'new@domain.com',
            'role'  => 'admin',
        ]);

        $this->assertTrue($user->exists());

        $this->assertEquals('new@domain.com', $user->email());
        $this->assertEquals('admin', $user->role());
    }

    public function testCreateUserWithUnicodeEmail()
    {
        // with Unicode email
        $user = User::create([
            'email' => 'test@exämple.com',
            'role'  => 'admin',
        ]);

        $this->assertTrue($user->exists());
        $this->assertSame('test@exämple.com', $user->email());
        $this->assertSame('admin', $user->role()->name());

        // with Punycode email
        $user = User::create([
            'email' => 'test@xn--tst-qla.com',
            'role'  => 'admin',
        ]);

        $this->assertTrue($user->exists());
        $this->assertSame('test@täst.com', $user->email());
        $this->assertSame('admin', $user->role()->name());
    }

    public function testCreateEditor()
    {
        $user = User::create([
            'email' => 'new@domain.com',
            'role'  => 'editor',
        ]);

        $this->assertTrue($user->exists());

        $this->assertEquals('new@domain.com', $user->email());
        $this->assertEquals('editor', $user->role());
    }

    public function testCreateWithContent()
    {
        $user = User::create([
            'email' => 'new@domain.com',
            'role'  => 'editor',
            'content' => [
                'a' => 'Custom A'
            ],
        ]);

        $this->assertEquals('Custom A', $user->a()->value());
    }

    public function testCreateWithDefaults()
    {
        $user = User::create([
            'email' => 'new@domain.com',
            'role'  => 'editor',
            'blueprint' => [
                'name' => 'editor',
                'fields' => [
                    'a'  => [
                        'type'    => 'text',
                        'default' => 'A'
                    ],
                    'b' => [
                        'type'    => 'textarea',
                        'default' => 'B'
                    ]
                ]
            ]
        ]);

        $this->assertEquals('A', $user->a()->value());
        $this->assertEquals('B', $user->b()->value());
    }

    public function testCreateWithDefaultsAndContent()
    {
        $user = User::create([
            'email' => 'new@domain.com',
            'role'  => 'editor',
            'content' => [
                'a' => 'Custom A'
            ],
            'blueprint' => [
                'name' => 'editor',
                'fields' => [
                    'a'  => [
                        'type'    => 'text',
                        'default' => 'A'
                    ],
                    'b' => [
                        'type'    => 'textarea',
                        'default' => 'B'
                    ]
                ]
            ]
        ]);

        $this->assertEquals('Custom A', $user->a()->value());
        $this->assertEquals('B', $user->b()->value());
    }

    public function testCreateWithContentMultilang()
    {
        $this->app = $this->app->clone([
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'en',
                    'default' => true,
                ],
                [
                    'code'    => 'de',
                ]
            ]
        ]);

        $user = User::create([
            'email' => 'new@domain.com',
            'role'  => 'editor',
            'content' => [
                'a' => 'a',
                'b' => 'b',
            ],
        ]);

        $this->assertTrue($user->exists());

        $this->assertEquals('a', $user->a()->value());
        $this->assertEquals('b', $user->b()->value());
    }

    public function testDelete()
    {
        $user = $this->app->user('editor@domain.com');
        $user->save();

        $this->assertFileExists($user->root() . '/user.txt');
        $user->delete();
        $this->assertFileDoesNotExist($user->root() . '/user.txt');
    }

    public function testUpdate()
    {
        $user = $this->app->user('editor@domain.com');
        $user = $user->update([
            'website' => $url = 'https://editor.com'
        ]);

        $this->assertEquals($url, $user->website()->value());
    }

    public function testUpdateWithAuthUser()
    {
        $app = new App([
            'users' => [
                [
                    'email' => 'admin@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        $user = $app->user('admin@getkirby.com');
        $user->loginPasswordless();
        $user->update([
            'website' => $url = 'https://getkirby.com'
        ]);
        $this->assertEquals($url, $app->user()->website()->value());
        $user->logout();
    }

    public function testChangeEmailHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'user.changeEmail:before' => function (User $user, $email) use ($phpunit, &$calls) {
                    $phpunit->assertSame('editor@domain.com', $user->email());
                    $phpunit->assertSame('another@domain.com', $email);
                    $calls++;
                },
                'user.changeEmail:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
                    $phpunit->assertSame('another@domain.com', $newUser->email());
                    $phpunit->assertSame('editor@domain.com', $oldUser->email());
                    $calls++;
                }
            ]
        ]);

        $user = $app->user('editor@domain.com');
        $user->changeEmail('another@domain.com');

        $this->assertSame(2, $calls);
    }

    public function testChangeLanguageHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'user.changeLanguage:before' => function (User $user, $language) use ($phpunit, &$calls) {
                    $phpunit->assertSame('en', $user->language());
                    $phpunit->assertSame('de', $language);
                    $calls++;
                },
                'user.changeLanguage:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
                    $phpunit->assertSame('de', $newUser->language());
                    $phpunit->assertSame('en', $oldUser->language());
                    $calls++;
                }
            ]
        ]);

        $user = $app->user('editor@domain.com');
        $user->changeLanguage('de');

        $this->assertSame(2, $calls);
    }

    public function testChangeNameHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'user.changeName:before' => function (User $user, $name) use ($phpunit, &$calls) {
                    $phpunit->assertNull($user->name()->value());
                    $phpunit->assertSame('Edith Thor', $name);
                    $calls++;
                },
                'user.changeName:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
                    $phpunit->assertSame('Edith Thor', $newUser->name()->value());
                    $phpunit->assertNull($oldUser->name()->value());
                    $calls++;
                }
            ]
        ]);

        $user = $app->user('editor@domain.com');
        $user->changeName('Edith Thor');

        $this->assertSame(2, $calls);
    }

    public function testChangePasswordHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'user.changePassword:before' => function (User $user, $password) use ($phpunit, &$calls) {
                    $phpunit->assertEmpty($user->password());
                    $phpunit->assertSame('topsecret2018', $password);
                    $calls++;
                },
                'user.changePassword:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
                    $phpunit->assertTrue($newUser->validatePassword('topsecret2018'));
                    $phpunit->assertEmpty($oldUser->password());
                    $calls++;
                }
            ]
        ]);

        $user = $app->user('editor@domain.com');
        $user->changePassword('topsecret2018');

        $this->assertSame(2, $calls);
    }

    public function testChangeRoleHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'user.changeRole:before' => function (User $user, $role) use ($phpunit, &$calls) {
                    $phpunit->assertSame('editor', $user->role()->name());
                    $phpunit->assertSame('admin', $role);
                    $calls++;
                },
                'user.changeRole:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
                    $phpunit->assertSame('admin', $newUser->role()->name());
                    $phpunit->assertSame('editor', $oldUser->role()->name());
                    $calls++;
                }
            ]
        ]);

        $user = $app->user('editor@domain.com');
        $user->changeRole('admin');

        $this->assertSame(2, $calls);
    }

    public function testCreateHooks()
    {
        $calls = 0;
        $phpunit= $this;
        $userInput = [
            'email' => 'new@domain.com',
            'role'  => 'admin',
            'model' => 'admin',
        ];

        $this->app->clone([
            'hooks' => [
                'user.create:before' => function (User $user, $input) use ($phpunit, $userInput, &$calls) {
                    $phpunit->assertSame('new@domain.com', $user->email());
                    $phpunit->assertSame('admin', $user->role()->name());
                    $phpunit->assertSame($userInput, $input);
                    $calls++;
                },
                'user.create:after' => function (User $user) use ($phpunit, &$calls) {
                    $phpunit->assertSame('new@domain.com', $user->email());
                    $phpunit->assertSame('admin', $user->role()->name());
                    $calls++;
                }
            ]
        ]);

        User::create($userInput);

        $this->assertSame(2, $calls);
    }

    public function testDeleteHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'user.delete:before' => function (User $user) use ($phpunit, &$calls) {
                    $phpunit->assertSame('editor@domain.com', $user->email());
                    $phpunit->assertSame('editor', $user->role()->name());
                    $calls++;
                },
                'user.delete:after' => function ($status, User $user) use ($phpunit, &$calls) {
                    $phpunit->assertTrue($status);
                    $phpunit->assertSame('editor@domain.com', $user->email());
                    $phpunit->assertSame('editor', $user->role()->name());
                    $calls++;
                }
            ]
        ]);

        $user = $app->user('editor@domain.com');
        $user->delete();

        $this->assertSame(2, $calls);
    }

    public function testUpdateHooks()
    {
        $calls = 0;
        $phpunit = $this;
        $input = [
            'website' => 'https://getkirby.com'
        ];

        $app = $this->app->clone([
            'hooks' => [
                'user.update:before' => function (User $user, $values, $strings) use ($phpunit, $input, &$calls) {
                    $phpunit->assertNull($user->website()->value());
                    $phpunit->assertSame($input, $values);
                    $phpunit->assertSame($input, $strings);
                    $calls++;
                },
                'user.update:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
                    $phpunit->assertSame('https://getkirby.com', $newUser->website()->value());
                    $phpunit->assertNull($oldUser->website()->value());
                    $calls++;
                }
            ]
        ]);

        $user = $app->user('editor@domain.com');
        $user->update($input);

        $this->assertSame(2, $calls);
    }
}
