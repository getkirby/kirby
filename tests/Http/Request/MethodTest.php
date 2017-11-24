<?php

namespace Kirby\Http\Request;

use PHPUnit\Framework\TestCase;

class MethodTest extends TestCase
{

    public function testConstruct()
    {
        $method = new Method();
        $this->assertEquals('GET', $method->name());
    }

    /**
     * @expectedException Exception
     *
     * @return [type] [description]
     */
    public function testConstructWithInvalidName()
    {
        new Method('foo');
    }

    public function testIs()
    {
        $method = new Method();
        $this->assertTrue($method->is('GET'));
        $this->assertTrue($method->is('get'));
        $this->assertFalse($method->is('post'));

        $method = new Method('POST');
        $this->assertTrue($method->is('POST'));
        $this->assertTrue($method->is('post'));
        $this->assertFalse($method->is('get'));
    }

    public function testName()
    {
        $types = [
            'GET',
            'HEAD',
            'POST',
            'PUT',
            'DELETE',
            'CONNECT',
            'OPTIONS',
            'TRACE',
            'PATCH'
        ];

        foreach ($types as $type) {
            $method = new Method($type);
            $this->assertEquals($type, $method->name());
            $this->assertEquals($type, $method->toString());
            $this->assertEquals($type, $method->__toString());
            $this->assertEquals($type, $method);
        }
    }
}
