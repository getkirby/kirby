<?php

namespace Kirby\Cms;

class UsersTest extends TestCase
{
    public function testAddUser()
    {
        $users = Users::factory([
            ['email' => 'a@getkirby.com']
        ]);

        $user = new User([
            'email' => 'b@getkirby.com'
        ]);

        $result = $users->add($user);

        $this->assertCount(2, $result);
        $this->assertEquals('a@getkirby.com', $result->nth(0)->email());
        $this->assertEquals('b@getkirby.com', $result->nth(1)->email());
    }

    public function testAddCollection()
    {
        $a = Users::factory([
            ['email' => 'a@getkirby.com']
        ]);

        $b = Users::factory([
            ['email' => 'b@getkirby.com'],
            ['email' => 'c@getkirby.com']
        ]);

        $c = $a->add($b);

        $this->assertCount(3, $c);
        $this->assertEquals('a@getkirby.com', $c->nth(0)->email());
        $this->assertEquals('b@getkirby.com', $c->nth(1)->email());
        $this->assertEquals('c@getkirby.com', $c->nth(2)->email());
    }

    public function testAddById()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                ['email' => 'a@getkirby.com'],
                ['email' => 'b@getkirby.com'],
                ['email' => 'c@getkirby.com'],
            ]
        ]);


        $users = $app->users()->limit(2);

        $this->assertCount(2, $users);

        $users = $users->add('c@getkirby.com');

        $this->assertCount(3, $users);
        $this->assertEquals('a@getkirby.com', $users->nth(0)->email());
        $this->assertEquals('b@getkirby.com', $users->nth(1)->email());
        $this->assertEquals('c@getkirby.com', $users->nth(2)->email());
    }

    public function testAddNull()
    {
        $users = new Users();
        $this->assertCount(0, $users);

        $users->add(null);

        $this->assertCount(0, $users);
    }

    public function testAddFalse()
    {
        $users = new Users();
        $this->assertCount(0, $users);

        $users->add(false);

        $this->assertCount(0, $users);
    }

    public function testAddInvalidObject()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('You must pass a Users or User object or an ID of an existing user to the Users collection');

        $site  = new Site();
        $users = new Users();
        $users->add($site);
    }

    public function testFind()
    {
        $users = new Users([
            new User(['email' => 'a@getkirby.com']),
            new User(['email' => 'B@getKirby.com']),
        ]);

        $first = $users->first();
        $last  = $users->last();

        $this->assertEquals($first, $users->find($first->id()));
        $this->assertEquals($last, $users->find($last->id()));
        $this->assertEquals($first, $users->find($first->email()));
        $this->assertEquals($last, $users->find($last->email()));
    }

    public function testFindByEmail()
    {
        $users = new Users([
            new User(['email' => 'a@getkirby.com']),
            new User(['email' => 'B@getKirby.com']),
        ]);

        $this->assertEquals('a@getkirby.com', $users->find('a@getkirby.com')->email());
        $this->assertEquals('a@getkirby.com', $users->find('A@getkirby.com')->email());
        $this->assertEquals('b@getkirby.com', $users->find('B@getkirby.com')->email());
        $this->assertEquals('b@getkirby.com', $users->find('b@getkirby.com')->email());
    }

    public function testCustomMethods()
    {
        Users::$methods = [
            'test' => function () {
                $i = 0;
                foreach ($this as $user) {
                    $i++;
                }
                return $i;
            }
        ];

        $users = new Users([
            new User(['email' => 'a@getkirby.com']),
            new User(['email' => 'B@getKirby.com']),
        ]);

        $this->assertEquals(2, $users->test());

        Users::$methods = [];
    }

    public function testRoles()
    {
        $app = new App([
            'users' => [
                ['email' => 'a@getkirby.com', 'role' => 'admin'],
                ['email' => 'b@getkirby.com', 'role' => 'editor'],
                ['email' => 'c@getkirby.com', 'role' => 'editor'],
            ],
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor']
            ]
        ]);

        $this->assertCount(1, $app->users()->role('admin'));
        $this->assertCount(2, $app->users()->role('editor'));
    }
}
