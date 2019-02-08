<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class RemoteTest extends TestCase
{
    public function setUp(): void
    {
        $this->defaults = Remote::$defaults;

        Remote::$defaults = array_merge($this->defaults, [
            'test' => true
        ]);
    }

    public function tearDown(): void
    {
        Remote::$defaults = $this->defaults;
    }

    public function testContent()
    {
        $request = Remote::put('https://getkirby.com');
        $this->assertEquals(null, $request->content());
    }

    public function testCode()
    {
        $request = Remote::put('https://getkirby.com');
        $this->assertEquals(null, $request->code());
    }

    public function testDelete()
    {
        $request = Remote::delete('https://getkirby.com');
        $this->assertEquals('DELETE', $request->method());
    }

    public function testGet()
    {
        $request = Remote::get('https://getkirby.com');
        $this->assertEquals('GET', $request->method());
    }

    public function testHead()
    {
        $request = Remote::head('https://getkirby.com');
        $this->assertEquals('HEAD', $request->method());
    }

    public function testHeaders()
    {
        $request = new Remote('https://getkirby.com');
        $this->assertEquals([], $request->headers());
    }

    public function testInfo()
    {
        $request = new Remote('https://getkirby.com');
        $this->assertEquals([], $request->info());
    }

    public function testPatch()
    {
        $request = Remote::patch('https://getkirby.com');
        $this->assertEquals('PATCH', $request->method());
    }

    public function testPost()
    {
        $request = Remote::post('https://getkirby.com');
        $this->assertEquals('POST', $request->method());
    }

    public function testPut()
    {
        $request = Remote::put('https://getkirby.com');
        $this->assertEquals('PUT', $request->method());
    }

    public function testRequest()
    {
        $request = new Remote($url = 'https://getkirby.com');

        $this->assertEquals($url, $request->url());
        $this->assertEquals('GET', $request->method());
    }
}
