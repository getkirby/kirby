<?php

namespace Kirby\Util;

use PHPUnit\Framework\TestCase;

class ObjectTest extends TestCase
{

    public function testCall()
    {
        $obj = new Object([
            'bastian' => 'allgeier',
            'foo'     => 'bar'
        ]);

        $this->assertEquals('allgeier', $obj->bastian());
        $this->assertEquals('bar', $obj->foo());
    }

    public function testInvalidKeys()
    {
        $array = [
            ''        => 'kirby',
            'bastian' => 'allgeier',
        ];

        $result = [
            'bastian' => 'allgeier',
        ];

        $obj = new Object($array);

        $this->assertEquals($result, $obj->toArray());
    }

    public function testSetGet()
    {
        $obj = new Object();
        $obj->set('bastian', 'allgeier');

        $this->assertEquals('allgeier', $obj->get('bastian'));
        $this->assertEquals('bar', $obj->get('foo', 'bar'));
        $this->assertEquals(null, $obj->get('foo'));
    }

    public function testArray()
    {
        $array = [
            'bastian' => 'allgeier',
            'foo'      => 'bar'
        ];

        $obj = new Object($array);

        $this->assertEquals($array, $obj->toArray());
        $this->assertEquals($array, $obj->__debuginfo());
    }
}
