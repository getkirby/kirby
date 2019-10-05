<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class SitePermissionsTest extends TestCase
{
    public function actionProvider()
    {
        return [
            ['changeTitle'],
            ['update'],
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

        $site  = new Site();
        $perms = $site->permissions();

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

        $site  = new Site();
        $perms = $site->permissions();

        $this->assertFalse($perms->can($action));
    }
}
