<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class RemoteTest extends TestCase
{
    protected $defaults;

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

    public function testOptionsHeaders()
    {
        $request = Remote::get('https://getkirby.com', [
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Charset: utf8'
            ]
        ]);
        $this->assertSame([
            'Accept: application/json',
            'Accept-Charset: utf8'
        ], $request->curlopt[CURLOPT_HTTPHEADER]);
    }

    public function testOptionsBasicAuth()
    {
        $request = Remote::get('https://getkirby.com', [
            'basicAuth' => 'user:pw'
        ]);
        $this->assertSame('user:pw', $request->curlopt[CURLOPT_USERPWD]);
    }

    public function testContent()
    {
        $request = Remote::put('https://getkirby.com');
        $this->assertSame(null, $request->content());
    }

    public function testCode()
    {
        $request = Remote::put('https://getkirby.com');
        $this->assertSame(null, $request->code());
    }

    public function testDelete()
    {
        $request = Remote::delete('https://getkirby.com');
        $this->assertSame('DELETE', $request->method());
    }

    public function testGet()
    {
        $request = Remote::get('https://getkirby.com');
        $this->assertSame('GET', $request->method());
    }

    public function testHead()
    {
        $request = Remote::head('https://getkirby.com');
        $this->assertSame('HEAD', $request->method());
    }

    public function testHeaders()
    {
        $request = new Remote('https://getkirby.com');
        $this->assertSame([], $request->headers());
    }

    public function testInfo()
    {
        $request = new Remote('https://getkirby.com');
        $this->assertSame([], $request->info());
    }

    public function testPatch()
    {
        $request = Remote::patch('https://getkirby.com');
        $this->assertSame('PATCH', $request->method());
    }

    public function testPost()
    {
        $request = Remote::post('https://getkirby.com');
        $this->assertSame('POST', $request->method());
    }

    public function testPut()
    {
        $request = Remote::put('https://getkirby.com');
        $this->assertSame('PUT', $request->method());
    }

    public function testRequest()
    {
        $request = new Remote($url = 'https://getkirby.com');

        $this->assertSame($url, $request->url());
        $this->assertSame('GET', $request->method());
    }
}
