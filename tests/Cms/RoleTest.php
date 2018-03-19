<?php

namespace Kirby\Cms;

class RoleTest extends TestCase
{

    public function app()
    {
        return new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);
    }

    public function testProps()
    {
        $role = new Role([
            'description' => 'Test',
            'name'  => 'admin',
            'title' => 'Admin'
        ]);

        $this->assertEquals('admin', $role->name());
        $this->assertEquals('Admin', $role->title());
        $this->assertEquals('Test', $role->description());
    }

    public function testFactory()
    {
        $app  = $this->app();
        $role = Role::factory('editor');

        $this->assertEquals('editor', $role->name());
        $this->assertEquals('Editor', $role->title());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The role "does-not-exist" does not exist
     *
     * @return void
     */
    public function testMissingRole()
    {
        $app  = $this->app();
        $role = Role::factory('does-not-exist');
    }

    public function testAdminFactory()
    {
        $app  = $this->app();
        $role = Role::factory('admin');

        $this->assertEquals('admin', $role->name());
        $this->assertEquals('Admin', $role->title());
    }

    public function testNobodyFactory()
    {
        $app  = $this->app();
        $role = Role::factory('nobody');

        $this->assertEquals('nobody', $role->name());
        $this->assertEquals('Nobody', $role->title());
    }

}
