<?php

namespace Kirby\Cms;

class BlueprintMockObject extends BlueprintObject
{

    use HasUnknownProperties;

    public function __construct(array $props = [])
    {
        $props = $this->extend($props);
        $this->setUnknownProperties($props);
    }

}


class BlueprintObjectTest extends TestCase
{

    public function setUp()
    {
        // reset all mixins
        BlueprintMockObject::mixins([]);
    }

    public function testDefaultSchema()
    {
        $object = new BlueprintMockObject();
        $this->assertArrayHasKey('locale', $object->toArray());
        $this->assertArrayHasKey('model', $object->toArray());
    }

    public function testMixin()
    {
        BlueprintMockObject::mixin('test', [
            'a' => 'Value A',
            'b' => 'Value B'
        ]);

        $object = new BlueprintMockObject([
            'extends' => 'test',
            'c'       => 'Value C'
        ]);

        $this->assertEquals('Value A', $object->a());
        $this->assertEquals('Value B', $object->b());
        $this->assertEquals('Value C', $object->c());
    }

    public function testOverloadMixin()
    {
        BlueprintMockObject::mixin('test', [
            'a' => 'Value A',
            'b' => 'Value B'
        ]);

        $object = new BlueprintMockObject([
            'extends' => 'test',
            'a'       => 'Overloaded A'
        ]);

        $this->assertEquals('Overloaded A', $object->a());
        $this->assertEquals('Value B', $object->b());
    }

}
