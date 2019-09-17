<?php

namespace Kirby\Http\Request;

use PHPUnit\Framework\TestCase;

class BodyTest extends TestCase
{
    public function testContents()
    {
        // default contents
        $body = new Body();
        $this->assertEquals('', $body->contents());

        // array content
        $contents = ['a' => 'a'];
        $body     = new Body($contents);
        $this->assertEquals($contents, $body->contents());

        // string
        $contents = 'foo';
        $body     = new Body($contents);
        $this->assertEquals($contents, $body->contents());

        // $_POST
        $body = new Body();
        $_POST = 'foo';
        $this->assertEquals('foo', $body->contents());
    }

    public function testData()
    {
        // default
        $data = [];
        $body = new Body();
        $this->assertEquals($data, $body->data());

        // array data
        $data = ['a' => 'a'];
        $body = new Body($data);
        $this->assertEquals($data, $body->data());

        // json data
        $data = ['a' => 'a'];
        $body = new Body(json_encode($data));
        $this->assertEquals($data, $body->data());

        // http query data
        $data = ['a' => 'a'];
        $body = new Body(http_build_query($data));
        $this->assertEquals($data, $body->data());

        // unparsable string
        $data = 'foo';
        $body = new Body($data);
        $this->assertEquals([], $body->data());
    }

    public function testToArrayAndDebuginfo()
    {
        $data = ['a' => 'a'];
        $body = new Body($data);
        $this->assertEquals($data, $body->toArray());
        $this->assertEquals($data, $body->__debugInfo());
    }

    public function testToJson()
    {
        $data = ['a' => 'a'];
        $body = new Body($data);
        $this->assertEquals(json_encode($data), $body->toJson());
    }

    public function testToString()
    {
        // default
        $body = new Body();
        $this->assertEquals('', $body->toString());
        $this->assertEquals('', $body->__toString());
        $this->assertEquals('', $body);

        // with data
        $string = 'foo=bar';
        $body   = new Body(['foo' => 'bar']);
        $this->assertEquals($string, $body->toString());
        $this->assertEquals($string, $body->__toString());
        $this->assertEquals($string, $body);
    }
}
