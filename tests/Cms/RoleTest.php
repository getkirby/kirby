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
        $role = Role::load(__DIR__ . '/fixtures/blueprints/users/editor.yml');

        $this->assertEquals('editor', $role->name());
        $this->assertEquals('Editor', $role->title());
    }

    /**
     * @expectedException Exception
     *
     * @return void
     */
    public function testMissingRole()
    {
        $app  = $this->app();
        $role = Role::load('does-not-exist');
    }

    public function testAdmin()
    {
        $app  = $this->app();
        $role = Role::admin();

        $this->assertEquals('admin', $role->name());
        $this->assertEquals('Admin', $role->title());
    }

    public function testNobody()
    {
        $app  = $this->app();
        $role = Role::nobody();

        $this->assertEquals('nobody', $role->name());
        $this->assertEquals('Nobody', $role->title());
    }
}
