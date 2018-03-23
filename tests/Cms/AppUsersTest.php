<?php

namespace Kirby\Cms;

class AppUsersTest extends TestCase
{

    public function testSet()
    {
        $app = new App([
            'users' => [
                [
                    'email' => 'user@getkirby.com'
                ]
            ]
        ]);

        $this->assertCount(1, $app->users());
        $this->assertEquals('user@getkirby.com', $app->users()->first()->email());
    }

    public function testLoad()
    {
        $app = new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);

        $this->assertCount(1, $app->users());
        $this->assertEquals('user@getkirby.com', $app->users()->first()->email());
    }

}
