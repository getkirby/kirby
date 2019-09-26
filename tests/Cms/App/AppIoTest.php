<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Http\Response;
use PHPUnit\Framework\TestCase;

class AppIoTest extends TestCase
{
    public function app()
    {
        return new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testException()
    {
        $response = $this->app()->io(new Exception('Nope'));

        $this->assertEquals(500, $response->code());
        $this->assertEquals('Nope', $response->body());
    }

    public function testExceptionWithInvalidHttpCode()
    {
        $response = $this->app()->io(new Exception('Nope', 8000));

        $this->assertEquals(500, $response->code());
        $this->assertEquals('Nope', $response->body());
    }

    public function testEmpty()
    {
        $response = $this->app()->io('');

        $this->assertEquals(404, $response->code());
        $this->assertEquals('Not found', $response->body());
    }

    public function testResponder()
    {
        $app   = $this->app();
        $input = $app->response()->code(201)->body('Test');

        $response = $app->io($input);

        $this->assertEquals(201, $response->code());
        $this->assertEquals('Test', $response->body());
    }

    public function testResponse()
    {
        $input = new Response([
            'code' => 200,
            'body' => 'Test'
        ]);

        $response = $this->app()->io($input);

        $this->assertEquals($input, $response);
    }

    public function testString()
    {
        $response = $this->app()->io('Test');

        $this->assertEquals(200, $response->code());
        $this->assertEquals('Test', $response->body());
    }

    public function testArray()
    {
        $response = $this->app()->io($array = ['foo' => 'bar']);

        $this->assertEquals(200, $response->code());
        $this->assertEquals(json_encode($array), $response->body());
    }
}
