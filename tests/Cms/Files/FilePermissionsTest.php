<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class FilePermissionsTest extends TestCase
{
    public function actionProvider()
    {
        return [
            ['changeName'],
            ['create'],
            ['delete'],
            ['replace'],
            ['update']
        ];
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithAdmin($action)
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $kirby->impersonate('kirby');

        $page = new Page([
            'slug' => 'test'
        ]);

        $file  = new File(['filename' => 'test.jpg', 'parent' => $page]);
        $perms = $file->permissions();

        $this->assertTrue($perms->can($action));
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithNobody($action)
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $page = new Page([
            'slug' => 'test'
        ]);

        $file  = new File(['filename' => 'test.jpg', 'parent' => $page]);
        $perms = $file->permissions();

        $this->assertFalse($perms->can($action));
    }
}
