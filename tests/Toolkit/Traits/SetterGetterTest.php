<?php

namespace Kirby\Toolkit\Traits;

use PHPUnit\Framework\TestCase;

class SetterGetterTest extends TestCase
{

    public function testSetGet()
    {
        $object = new SetterGetterTraitsUser();

        $object->set('foo', 'bar');
        $this->assertEquals('bar', $object->get('foo'));
    }

    public function testMagicSetGet()
    {
        $object = new SetterGetterTraitsUser();

        $object->foo('bar');
        $this->assertEquals('bar', $object->foo());
    }
}
