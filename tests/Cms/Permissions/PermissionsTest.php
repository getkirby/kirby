<?php

namespace Kirby\Cms;

class PermissionsTest extends TestCase
{
    public function actions()
    {
        return [
            ['files', 'changeName'],
            ['files', 'create'],
            ['files', 'delete'],
            ['files', 'replace'],
            ['files', 'update'],

            ['pages', 'changeSlug'],
            ['pages', 'changeStatus'],
            ['pages', 'changeTemplate'],
            ['pages', 'changeTitle'],
            ['pages', 'create'],
            ['pages', 'delete'],
            ['pages', 'sort'],
            ['pages', 'update'],

            ['site', 'changeTitle'],
            ['site', 'update'],

            ['users', 'changeEmail'],
            ['users', 'changeLanguage'],
            ['users', 'changeName'],
            ['users', 'changePassword'],
            ['users', 'changeRole'],
            ['users', 'create'],
            ['users', 'delete'],
            ['users', 'update'],

            ['user', 'changeEmail'],
            ['user', 'changeLanguage'],
            ['user', 'changeName'],
            ['user', 'changePassword'],
            ['user', 'changeRole'],
            ['user', 'delete'],
            ['user', 'update'],
        ];
    }


    /**
     * @dataProvider actions
     */
    public function testActions(string $category, $action)
    {

        // default
        $p = new Permissions();
        $this->assertTrue($p->for($category, $action));

        // globally disabled
        $p = new Permissions([$category => false]);
        $this->assertFalse($p->for($category, $action));

        // monster off switch
        $p = new Permissions(false);
        $this->assertFalse($p->for($category, $action));

        // monster on switch
        $p = new Permissions(true);
        $this->assertTrue($p->for($category, $action));

        // locally disabled
        $p = new Permissions([
            $category => [
                $action => false
            ]
        ]);

        $this->assertFalse($p->for($category, $action));

        // locally enabled
        $p = new Permissions([
            $category => [
                $action => true
            ]
        ]);

        $this->assertTrue($p->for($category, $action));
    }
}
