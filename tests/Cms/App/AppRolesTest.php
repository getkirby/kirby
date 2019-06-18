<?php

namespace Kirby\Cms;

class AppRolesTest extends TestCase
{
    public function testSet()
    {
        $app = new App([
            'roles' => [
                [
                    'name'  => 'editor',
                    'title' => 'Editor'
                ]
            ]
        ]);

        $this->assertCount(2, $app->roles());
        $this->assertEquals('editor', $app->roles()->last()->name());
    }

    public function testLoad()
    {
        $app = new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);

        $this->assertCount(2, $app->roles());
        $this->assertEquals('editor', $app->roles()->last()->name());
    }
}
