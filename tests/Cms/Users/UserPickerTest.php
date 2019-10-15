<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class UserPickerTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                ['email' => 'a@getkirby.com'],
                ['email' => 'b@getkirby.com'],
                ['email' => 'c@getkirby.com']
            ]
        ]);

        $this->app->impersonate('kirby');
    }

    public function testDefaults()
    {
        $picker = new UserPicker();

        $this->assertCount(3, $picker->items());
    }

    public function testQuery()
    {
        $picker = new UserPicker([
            'query' => 'kirby.users.offset(1)'
        ]);

        $this->assertCount(2, $picker->items());
    }
}
