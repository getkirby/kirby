<?php

namespace Kirby\Cms;

class UsersTest extends TestCase
{
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
}
