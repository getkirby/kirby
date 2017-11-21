<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{

    public function testBody()
    {
        $response = new Response;
        $this->assertEquals('', $response->body());
        $this->assertEquals('test', $response->body('test'));
        $this->assertEquals('test', $response->body());
        $this->assertEquals('foobar', $response->body(['foo', 'bar']));
    }

    public function testHeaders()
    {
        $response = new Response;
        $this->assertEquals([], $response->headers());
        $this->assertEquals(['test' => 'test'], $response->headers(['test' => 'test']));
        $this->assertEquals(['test' => 'test'], $response->headers());
    }

    public function testHeader()
    {
        $response = new Response;
        $this->assertNull($response->header('test'));
        $this->assertEquals('test', $response->header('test', 'test'));
        $this->assertEquals('test', $response->header('test'));
    }

    public function testType()
    {
        $response = new Response;
        $this->assertEquals('text/html', $response->type());
        $this->assertEquals('image/jpeg', $response->type('image/jpeg'));
        $this->assertEquals('image/jpeg', $response->type());
    }

    public function testCharset()
    {
        $response = new Response;
        $this->assertEquals('UTF-8', $response->charset());
        $this->assertEquals('test', $response->charset('test'));
        $this->assertEquals('test', $response->charset());
    }

    public function testCode()
    {
        $response = new Response;
        $this->assertEquals(200, $response->code());
        $this->assertEquals(404, $response->code(404));
        $this->assertEquals(404, $response->code());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSend()
    {
        $response = new Response('test');
        $response->header('foo', 'bar');

        ob_start();

        echo $response->send();

        $code = http_response_code();
        $body = ob_get_contents();

        ob_end_clean();

        $this->assertEquals($body, 'test');
        $this->assertEquals($code, 200);
    }

    /**
     * @runInSeparateProcess
     */
    public function testToString()
    {
        $response = new Response('test');
        $response->header('foo', 'bar');

        ob_start();

        echo $response;

        $code = http_response_code();
        $body = ob_get_contents();

        ob_end_clean();

        $this->assertEquals($body, 'test');
        $this->assertEquals($code, 200);
    }

    public function testToArray()
    {
        // default setup
        $response = new Response;
        $expected = [
            'type'    => 'text/html',
            'charset' => 'UTF-8',
            'code'    => 200,
            'headers' => [],
            'body'    => '',
        ];

        $this->assertEquals($expected, $response->toArray());
    }
}
