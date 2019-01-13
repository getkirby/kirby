<?php

namespace Kirby\Cms;

class RolesTest extends TestCase
{
    public function testFactory()
    {
        $roles = Roles::factory([
            [
                'name'  => 'editor',
                'title' => 'Editor'
            ]
        ]);

        $this->assertInstanceOf(Roles::class, $roles);

        // should contain the editor role from fixtures and the default admin role
        $this->assertCount(2, $roles);
        $this->assertEquals('admin', $roles->first()->name());
        $this->assertEquals('editor', $roles->last()->name());
    }

    public function testLoad()
    {
        $roles = Roles::load(__DIR__ . '/fixtures/blueprints/users');

        $this->assertInstanceOf(Roles::class, $roles);

        // should contain the editor role from fixtures and the default admin role
        $this->assertCount(2, $roles);
        $this->assertEquals('admin', $roles->first()->name());
        $this->assertEquals('editor', $roles->last()->name());
    }

    public function testLoadFromPlugins()
    {
        $app = new App([
            'blueprints' => [
                'users/admin' => [
                    'name'  => 'admin',
                    'title' => 'Admin'
                ],
                'users/editor' => [
                    'name'  => 'editor',
                    'title' => 'Editor'
                ],
            ]
        ]);

        $roles = Roles::load();

        $this->assertCount(2, $roles);
        $this->assertEquals('admin', $roles->first()->name());
        $this->assertEquals('editor', $roles->last()->name());
    }
}
