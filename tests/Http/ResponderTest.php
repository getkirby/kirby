<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class ResponderTest extends TestCase
{

    public function testHandleString()
    {
        $responder = new Responder;
        $response  = $responder->handle('Test');

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals('Test', $response->body());
        $this->assertEquals(200, $response->code());
    }

    public function testHandleInt()
    {
        $responder = new Responder;
        $response  = $responder->handle(404);

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals('', $response->body());
        $this->assertEquals(404, $response->code());
    }

    public function testHandleArray()
    {
        $responder = new Responder;
        $response  = $responder->handle(['a' => 'a']);

        $this->assertInstanceOf('Kirby\Http\Response\Json', $response);
        $this->assertEquals('{"a":"a"}', $response->body());
        $this->assertEquals(200, $response->code());
    }

    public function testHandleTrue()
    {
        $responder = new Responder;
        $response  = $responder->handle(true);

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals('', $response->body());
        $this->assertEquals(200, $response->code());
    }

    public function testHandleFalse()
    {
        $responder = new Responder;
        $response  = $responder->handle(false);

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals('Not found', $response->body());
        $this->assertEquals(404, $response->code());
    }

    public function testHandleObject()
    {
        $responder = new Responder;
        $response  = $responder->handle(new \stdClass);

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals('Unexpected object: stdClass', $response->body());
        $this->assertEquals(500, $response->code());
    }

    public function testHandleResponse()
    {
        $responder = new Responder;
        $input     = new Response;
        $response  = $responder->handle($input);

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals($input, $response);
    }

    public function testHandleNull()
    {
        $responder = new Responder;
        $response  = $responder->handle(null);

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals('Not found', $response->body());
        $this->assertEquals(404, $response->code());
    }

    public function testHandleUnkown()
    {
        $responder = new Responder;
        $response  = $responder->handle(1.234);

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals('Unexpected input: double', $response->body());
        $this->assertEquals(500, $response->code());
    }

    public function testOn()
    {
        $responder = new Responder;
        $responder->on('string', function ($input) {
            return new Response(strtoupper($input), 'text/plain', 202);
        });

        $response = $responder->handle('some string');

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals('SOME STRING', $response->body());
        $this->assertEquals('text/plain', $response->type());
        $this->assertEquals(202, $response->code());
    }

    public function testCustomHandler()
    {
        $responder = new Responder([
            'true' => function () {
                return new Response('Success');
            }
        ]);

        $response = $responder->handle(true);

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertEquals('Success', $response->body());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Undefined Responder handler: kirby
     */
    public function testUndefinedHandler()
    {
        $responder  = new Responder();
        $reflection = new \ReflectionClass(get_class($responder));
        $method = $reflection->getMethod('trigger');
        $method->setAccessible(true);

        $method->invokeArgs($responder, ['kirby', null]);
    }
}
