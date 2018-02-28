<?php

namespace Kirby\Cms;

class RegistryTest extends TestCase
{

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid registry entry type "foo"
     */
    public function testSetWithInvalidType()
    {
        $registry = new Registry;
        $registry->set('foo', 'bar');
    }

    public function entryProvider()
    {
        return [
            ['blueprint', 'test', '/test.yml'],
            ['collection', 'test', function () {}],
            ['controller', 'test', function () {}],
            ['field', 'test', '/field/directory'],
            ['fieldMethod', 'test', function () {}],
            ['fileMethod', 'test', function () {}],
            ['filesMethod', 'test', function () {}],
            ['pageMethod', 'test', function () {}],
            ['pageModel', 'test', 'MyModelClass'],
            ['pagesMethod', 'test', function () {}],
            ['option', 'test', 'anything'],
            ['route', 'test', []],
            ['siteMethod', 'test', function () {}],
            ['snippet', 'test', '/snippet.php'],
            ['tag', 'test', []],
            ['template', 'test', '/template.php'],
            ['validator', 'test', function () {}],
            ['widget', 'test', '/widget/directory'],
        ];
    }

    /**
     * @dataProvider entryProvider
     */
    public function testSetAndGet($type, $name, $entry)
    {
        $registry = new Registry;
        $registry->set($type, $name, $entry);

        $this->assertEquals($entry, $registry->get($type, $name));
    }

    public function testHook()
    {
        $registry = new Registry;
        $registry->set('hook', 'test', $closure = function () {});

        $expected = [$closure];

        $this->assertEquals($expected, $registry->get('hook', 'test'));

        $expected = [
            'test' => [$closure]
        ];

        $this->assertEquals($expected, $registry->get('hook'));
    }

    public function testSetAndGetRouteWithoutName()
    {
        $registry = new Registry;
        $registry->set('route', $route = [
            'pattern' => 'my/route',
            'action'  => function () {}
        ]);

        $this->assertEquals($route, $registry->get('route', 'my/route'));
    }

    public function testEntriesForType()
    {
        $registry = new Registry;
        $registry->set('template', 'test', 'test.php');

        $this->assertEquals(['test' => 'test.php'], $registry->get('template'));
    }

}
