<?php

namespace Kirby\Http;

use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Http\Request\Auth\BearerAuth;
use Kirby\Http\Request\Body;
use Kirby\Http\Request\Files;
use Kirby\Http\Request\Query;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testCustomProps()
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

        $this->assertTrue($request->is('POST'));
        $this->assertEquals('a', $request->body()->get('a'));
        $this->assertEquals('b', $request->query()->get('b'));
        $this->assertEquals($file, $request->file('upload'));
    }

    public function testData()
    {
        $request = new Request([
            'body'   => ['a' => 'a'],
            'query'  => ['b' => 'b'],
        ]);

        $this->assertEquals(['a' => 'a', 'b' => 'b'], $request->data());
        $this->assertEquals('a', $request->get('a'));
        $this->assertEquals('b', $request->get('b'));
        $this->assertEquals(null, $request->get('c'));
    }

    public function test__debuginfo()
    {
        $request = new Request();
        $info    = $request->__debugInfo();

        $this->assertArrayHasKey('body', $info);
        $this->assertArrayHasKey('query', $info);
        $this->assertArrayHasKey('files', $info);
        $this->assertArrayHasKey('method', $info);
        $this->assertArrayHasKey('url', $info);
    }

    public function testAuthMissing()
    {
        $request = new Request();
        $this->assertFalse($request->auth());
    }

    public function testBasicAuth()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode($credentials = 'testuser:testpass');

        $request = new Request();

        $this->assertInstanceOf(BasicAuth::class, $request->auth());
        $this->assertEquals('testuser', $request->auth()->username());
        $this->assertEquals('testpass', $request->auth()->password());

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function testBearerAuth()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer abcd';

        $request = new Request();

        $this->assertInstanceOf(BearerAuth::class, $request->auth());
        $this->assertEquals('abcd', $request->auth()->token());

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function testUnknownAuth()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Unknown abcd';

        $request = new Request();

        $this->assertFalse($request->auth());

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function testMethod()
    {
        $request = new Request();

        $this->assertInstanceOf(Query::class, $request->query());
        $this->assertInstanceOf(Body::class, $request->body());
        $this->assertInstanceOf(Files::class, $request->files());
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
        $this->assertTrue($request->is('GET'));
    }

    public function testIsWithLowerCaseInput()
    {
        $request = new Request();
        $this->assertTrue($request->is('get'));
    }

    public function testUrl()
    {
        $request = new Request();
        $this->assertInstanceOf(Uri::class, $request->url());
    }

    public function testUrlUpdates()
    {
        $request = new Request();

        $uriBefore = $request->url();

        $clone = $request->url([
            'host'  => 'getkirby.com',
            'path'  => 'yay',
            'query' => ['foo' => 'bar']
        ]);

        $uriAfter = $request->url();

        $this->assertNotEquals($uriBefore, $clone);
        $this->assertEquals($uriBefore, $uriAfter);
        $this->assertEquals('http://getkirby.com/yay?foo=bar', $clone->toString());
    }

    public function testPath()
    {
        $request = new Request();
        $this->assertInstanceOf(Path::class, $request->path());
    }

    public function testDomain()
    {
        $request = new Request([
            'url' => 'https://getkirby.com/a/b'
        ]);

        $this->assertEquals('getkirby.com', $request->domain());
    }
}
