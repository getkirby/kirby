<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    public function testCustomData()
    {
        $file = [
            'name'     => 'test.txt',
            'tmp_name' => '/tmp/abc',
            'size'     => 123,
            'error'    => 0
        ];

        $request = new Request([
            'method' => 'POST',
            'body'   => ['a' => 'a'],
            'query'  => ['b' => 'b'],
            'files'  => ['upload' => $file]
        ]);

        $this->assertTrue($request->is('post'));
        $this->assertEquals('a', $request->body()->get('a'));
        $this->assertEquals('b', $request->query()->get('b'));
        $this->assertEquals($file, $request->file('upload'));
    }

    public function testMethod()
    {
        $request = new Request();

        $this->assertInstanceOf('Kirby\Http\Request\Method', $request->method());
        $this->assertInstanceOf('Kirby\Http\Request\Query', $request->query());
        $this->assertInstanceOf('Kirby\Http\Request\Body', $request->body());
        $this->assertInstanceOf('Kirby\Http\Request\Files', $request->files());
    }

    public function testQuery()
    {
        $request = new Request();
        $this->assertInstanceOf('Kirby\Http\Request\Query', $request->query());
    }


    public function testBody()
    {
        $request = new Request();
        $this->assertInstanceOf('Kirby\Http\Request\Body', $request->body());
    }


    public function testFiles()
    {
        $request = new Request();
        $this->assertInstanceOf('Kirby\Http\Request\Files', $request->files());
    }

    public function testFile()
    {
        $request = new Request();
        $this->assertNull($request->file('test'));
    }

    public function testIs()
    {
        $request = new Request();
        $this->assertTrue($request->is('get'));
    }
}
