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
            ['hook', 'test', function () {}],
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

    public function multipleEntryProvider()
    {
        return [
            ['blueprint', ['a', 'b'] , '/test.yml'],
            ['controller', ['a', 'b'], function () {}],
            ['fieldMethod', ['a', 'b'], function () {}],
            ['fieldMethod', ['a', 'b'], function () {}],
            ['fileMethod', ['a', 'b'], function () {}],
            ['filesMethod', ['a', 'b'], function () {}],
            ['hook', ['a', 'b'], function () {}],
            ['pageMethod', ['a', 'b'], function () {}],
            ['pageModel', ['a', 'b'], 'MyModelClass'],
            ['pagesMethod', ['a', 'b'], function () {}],
            ['route', ['a', 'b'], []],
            ['siteMethod', ['a', 'b'], function () {}],
            ['template', ['a', 'b'], '/template.php'],
        ];
    }

    /**
     * @dataProvider multipleEntryProvider
     */
    public function testMultipleSetAndGet($type, array $names, $entry)
    {
        $registry = new Registry;
        $registry->set($type, $names, $entry);

        foreach ($names as $name) {
            $this->assertEquals($entry, $registry->get($type, $name));
        }
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

        $this->assertEquals(['test' => 'test.php'], $registry->entries('template'));
    }

}
