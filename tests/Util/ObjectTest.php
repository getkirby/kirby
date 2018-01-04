<?php

namespace Kirby\Util;

use PHPUnit\Framework\TestCase;

class ObjectTest extends TestCase
{

    public function testCall()
    {
        $obj = new Object([
            'name' => 'homer',
            'foo'  => 'bar'
        ]);

        $this->assertEquals('homer', $obj->name());
        $this->assertEquals('bar', $obj->foo());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid property key: ""
     */
    public function testInvalidKeys()
    {
        $obj = new Object([
            '' => 'kirby',
        ]);
    }

    public function testSetGet()
    {
        $obj = new Object;
        $obj->name = 'homer';
        $obj->email('homer@simpson.com');

        $this->assertEquals('homer', $obj->name());
        $this->assertEquals('homer', $obj->name);
        $this->assertEquals('homer@simpson.com', $obj->email());
        $this->assertEquals('homer@simpson.com', $obj->email);
        $this->assertEquals(null, $obj->foo());
        $this->assertEquals(null, $obj->foo);
    }

    public function testArray()
    {
        $array = [
            'name' => 'homer',
            'foo'  => 'bar'
        ];

        $obj = new Object($array);

        $this->assertEquals($array, $obj->toArray());
        $this->assertEquals($array, $obj->__debuginfo());
    }
}
