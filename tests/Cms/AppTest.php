<?php

namespace Kirby\Cms;

class AppTest extends TestCase
{

    public function testDefaultRoles()
    {
        $app = new App([
            'roots' => [
                'site' => __DIR__ . '/does-not-exist'
            ]
        ]);

        $this->assertInstanceOf(Roles::class, $app->roles());
    }

    public function testRolesFromFixtures()
    {
        $app = new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);

        $this->assertInstanceOf(Roles::class, $app->roles());
    }

}
