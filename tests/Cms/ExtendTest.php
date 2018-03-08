<?php

namespace Kirby\Cms;

class ExtendTest extends TestCase
{

    public function testBlueprints()
    {
        $result = Extend::blueprints($expected = [
            'a' => 'a.yml',
            'b' => 'b.yml'
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testCollections()
    {
        $result = Extend::collections($expected = [
            'a' => function () {},
            'b' => function () {}
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testControllers()
    {
        $result = Extend::controllers($expected = [
            'a' => function () {},
            'b' => function () {}
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testFields()
    {
        $result = Extend::fields([
            'a' => 'a',
            'b' => 'b'
        ]);

        $expected = [
            'a' => ['class' => 'a', 'plugin' => null],
            'b' => ['class' => 'b', 'plugin' => null]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testFieldMethods()
    {
        $result = Extend::fieldMethods($expected = [
            'a' => function () {},
            'b' => function () {}
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testHooksMultiple()
    {
        $result = Extend::hooks([
            'page.delete:before' => $beforeHook = function () {},
            'page.delete:after'  => $afterHooks = [
                function () {},
                function () {}
            ]
        ]);

        $expected = [
            'page.delete:before' => [$beforeHook],
            'page.delete:after'  => $afterHooks
        ];

        $this->assertEquals($expected, $result);
    }

    public function testHooksSingle()
    {
        $result = Extend::hooks([
            'page.delete:after' => $hook = function () {

            }
        ]);

        $expected = [
            'page.delete:after' => [$hook]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testPageModels()
    {
        $result = Extend::pageModels($expected = [
            'a' => 'a',
            'b' => 'b'
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testOptions()
    {
        $result = Extend::options($expected = [
            'a' => 'a',
            'b' => ['a', 'b']
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testRoutes()
    {
        $result = Extend::routes($expected = [
            [
                'pattern' => 'a',
                'action'  => function () {}
            ]
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testSnippets()
    {
        $result = Extend::snippets($expected = [
            'a' => 'a.php',
            'b' => 'b.php'
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testTags()
    {
        $result = Extend::tags($expected = [
            'a' => 'a',
            'b' => 'b'
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testTemplates()
    {
        $result = Extend::templates($expected = [
            'a' => 'a.php',
            'b' => 'b.php'
        ]);

        $this->assertEquals($expected, $result);
    }

}
