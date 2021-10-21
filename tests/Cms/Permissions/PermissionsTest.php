<?php

namespace Kirby\Cms;

class PermissionsTest extends TestCase
{
    public function tearDown(): void
    {
        Permissions::$extendedActions = [];
    }

    public function actionsProvider()
    {
        return [
            ['files', 'changeName'],
            ['files', 'create'],
            ['files', 'delete'],
            ['files', 'read'],
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
     * @dataProvider actionsProvider
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

    public function testExtendActions()
    {
        Permissions::$extendedActions = [
            'test-category' => [
                'test-action' => true,
                'another'     => false
            ]
        ];

        // default values
        $permissions = new Permissions();
        $this->assertTrue($permissions->for('test-category', 'test-action'));
        $this->assertFalse($permissions->for('test-category', 'another'));
        $this->assertFalse($permissions->for('test-category', 'does-not-exist'));

        // overridden values
        $permissions = new Permissions([
            'test-category' => [
                '*'       => false,
                'another' => true
            ]
        ]);
        $this->assertFalse($permissions->for('test-category', 'test-action'));
        $this->assertTrue($permissions->for('test-category', 'another'));
        $this->assertFalse($permissions->for('test-category', 'does-not-exist'));
    }

    public function testExtendActionsCoreOverride()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The action pages is already a core action');

        Permissions::$extendedActions = [
            'pages' => [
                'test-action' => true
            ]
        ];

        new Permissions();
    }

    /**
     * @todo Remove in 3.7
     */
    public function testSettingsFallback()
    {
        $permissions = new Permissions([
            'access' => [
                'settings' => false
            ]
        ]);

        $this->assertFalse($permissions->for('access.system'));
    }
}
