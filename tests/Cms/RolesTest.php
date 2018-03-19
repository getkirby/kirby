<?php

namespace Kirby\Cms;

class RolesTest extends TestCase
{

    public function testDefaultRoles()
    {
        new App([
            'roots' => [
                'site' => __DIR__ . '/does-not-exist'
            ]
        ]);

        $roles = Roles::factory();

        $this->assertInstanceOf(Roles::class, $roles);

        // should only contain the admin role
        $this->assertCount(1, $roles);
        $this->assertEquals('admin', $roles->first()->name());
    }

    public function testFactory()
    {
        new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);

        $roles = Roles::factory();

        $this->assertInstanceOf(Roles::class, $roles);

        // should contain the editor role from fixtures and the default admin role
        $this->assertCount(2, $roles);
        $this->assertEquals('admin', $roles->first()->name());
        $this->assertEquals('editor', $roles->last()->name());
    }

}
