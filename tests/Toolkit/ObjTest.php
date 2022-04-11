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

    public function test__getMultiple()
    {
        $obj = new Obj([
            'one' => 'first',
            'two' => 'second',
            'three' => 'third'
        ]);

        $this->assertEquals('first', $obj->get('one'));
        $this->assertEquals(['one' => 'first', 'three' => 'third'], $obj->get(['one', 'three']));
        $this->assertEquals(['one' => 'first', 'three' => 'third', 'eight' => null], $obj->get(['one', 'three', 'eight']));
        $this->assertEquals($obj->toArray(), $obj->get(['one', 'two', 'three']));
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
