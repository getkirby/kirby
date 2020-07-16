<?php

namespace Kirby\Http\Request;

use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testData()
    {
        // default
        $query = new Query();
        $this->assertEquals([], $query->data());

        // custom array
        $data  = ['foo' => 'bar'];
        $query = new Query($data);
        $this->assertEquals($data, $query->data());

        // custom string
        $string = 'foo=bar&kirby[]=bastian&kirby[]=allgeier';
        $data  = ['foo' => 'bar', 'kirby' => ['bastian', 'allgeier']];
        $query = new Query($string);
        $this->assertEquals($data, $query->data());
    }

    public function testIsEmpty()
    {
        // without data
        $query = new Query();
        $this->assertTrue($query->isEmpty());
        $this->assertFalse($query->isNotEmpty());

        // with data
        $query = new Query(['foo' => 'bar']);
        $this->assertFalse($query->isEmpty());
        $this->assertTrue($query->isNotEmpty());
    }

    public function testGet()
    {
        // default
        $query = new Query();
        $this->assertNull($query->get('foo'));

        // single get
        $query = new Query(['foo' => 'bar']);
        $this->assertEquals('bar', $query->get('foo'));

        // multiple gets
        $query = new Query(['a' => 'a', 'b' => 'b']);
        $this->assertEquals(['a' => 'a', 'b' => 'b', 'c' => null], $query->get(['a', 'b', 'c']));
    }

    public function testToString()
    {
        // default
        $query = new Query();
        $this->assertEquals('', $query->toString());
        $this->assertEquals('', $query->__toString());
        $this->assertEquals('', $query);

        // custom
        $query = new Query(['foo' => 'bar']);
        $this->assertEquals('foo=bar', $query->toString());
        $this->assertEquals('foo=bar', $query->__toString());
        $this->assertEquals('foo=bar', $query);
    }

    public function testToArrayAndDebuginfo()
    {
        $data  = ['a' => 'a'];
        $query = new Query($data);
        $this->assertEquals($data, $query->toArray());
        $this->assertEquals($data, $query->__debugInfo());
    }

    public function testToJson()
    {
        $data  = ['a' => 'a'];
        $query = new Query($data);
        $this->assertEquals(json_encode($data), $query->toJson());
    }
}
