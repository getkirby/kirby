<?php

namespace Kirby\Cms;

class BlueprintObjectTest extends TestCase
{

    public function setUp()
    {
        // reset all mixins
        BlueprintObject::mixins([]);
    }

    public function testDefaultSchema()
    {
        $object = new BlueprintObject();
        $this->assertEquals([], $object->toArray());
    }

    public function testMixin()
    {
        BlueprintObject::mixin('test', [
            'a' => 'Value A',
            'b' => 'Value B'
        ]);

        $object = new BlueprintObject([
            'extends' => 'test',
            'c'       => 'Value C'
        ]);

        $this->assertEquals('Value A', $object->a());
        $this->assertEquals('Value B', $object->b());
        $this->assertEquals('Value C', $object->c());
    }

    public function testOverloadMixin()
    {
        BlueprintObject::mixin('test', [
            'a' => 'Value A',
            'b' => 'Value B'
        ]);

        $object = new BlueprintObject([
            'extends' => 'test',
            'a'       => 'Overloaded A'
        ]);

        $this->assertEquals('Overloaded A', $object->a());
        $this->assertEquals('Value B', $object->b());
    }

}
