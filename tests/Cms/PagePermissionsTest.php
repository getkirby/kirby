<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PagePermissionsTest extends TestCase
{

    public function actionProvider()
    {
        return [
            ['changeSlug'],
            ['changeStatus'],
            // ['changeTemplate'], Returns false because of only one blueprint
            ['changeTitle'],
            ['create'],
            ['delete'],
            ['preview'],
            ['sort'],
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

        $page  = new Page(['slug' => 'test']);
        $perms = $page->permissions();

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

        $page  = new Page(['slug' => 'test']);
        $perms = $page->permissions();

        $this->assertFalse($perms->can($action));
    }

}
