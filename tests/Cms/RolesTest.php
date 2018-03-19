<?php

namespace Kirby\Cms;

class RolesTest extends TestCase
{

    public function testFactory()
    {
        new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);

        $roles = Roles::factory();

        $this->assertInstanceOf(Roles::class, $roles);
        $this->assertCount(1, $roles);
    }

}
