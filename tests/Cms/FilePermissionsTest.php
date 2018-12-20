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

        $file  = new File(['filename' => 'test.jpg']);
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

        $file  = new File(['filename' => 'test.jpg']);
        $perms = $file->permissions();

        $this->assertFalse($perms->can($action));
    }
}
