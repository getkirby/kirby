<?php

namespace Kirby\Cms;

class BlueprintMockObject extends BlueprintObject
{
}

class BlueprintObjectTest extends TestCase
{

    public function testDefaultSchema()
    {
        $object = new BlueprintMockObject();
        $this->assertArrayHasKey('model', $object->toArray());
    }

}
