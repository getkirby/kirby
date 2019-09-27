<?php

namespace Kirby\Toolkit;

class ObjTest extends TestCase
{
    public function test__call()
    {
        $obj = new Obj([
            'foo' => 'bar'
        ]);

        $this->assertEquals('bar', $obj->foo());
    }

    public function test__get()
    {
        $obj = new Obj();
        $this->assertNull($obj->foo);
    }

    public function testToArray()
    {
        $obj = new Obj($expected = [
            'foo' => 'bar'
        ]);

        $this->assertEquals($expected, $obj->toArray());
    }

    public function testToArrayWithChild()
    {
        $parent = new Obj([
            'child' => new Obj([
                'foo' => 'bar'
            ])
        ]);

        $expected = [
            'child' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertEquals($expected, $parent->toArray());
    }

    public function testToJson()
    {
        $obj = new Obj($expected = [
            'foo' => 'bar'
        ]);

        $this->assertEquals(json_encode($expected), $obj->toJson());
    }

    public function test__debuginfo()
    {
        $obj = new Obj($expected = [
            'foo' => 'bar'
        ]);

        $this->assertEquals($expected, $obj->__debugInfo());
    }
}
