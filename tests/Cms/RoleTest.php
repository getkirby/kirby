<?php

namespace Kirby\Cms;

class RoleTest extends TestCase
{

    public function testProps()
    {
        $role = new Role([
            'name'  => 'admin',
            'title' => 'Admin'
        ]);

        $this->assertEquals('admin', $role->name());
        $this->assertEquals('Admin', $role->title());
    }

    public function testFactory()
    {
        new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);

        $role = Role::factory('editor');

        $this->assertEquals('editor', $role->name());
        $this->assertEquals('Editor', $role->title());
    }

}
