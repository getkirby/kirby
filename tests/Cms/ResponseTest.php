<?php

namespace Kirby\Cms;

class ResponseTest extends TestCase
{

    public function testStringResponse()
    {
        $result = Response::for('test');

        $this->assertEquals('test', $result->body());
        $this->assertEquals(200, $result->code());
        $this->assertEquals('text/html', $result->type());
    }

    public function testArrayResponse()
    {
        $input  = ['test' => 'response'];
        $result = Response::for($input);

        $this->assertEquals(json_encode($input), $result->body());
        $this->assertEquals(200, $result->code());
        $this->assertEquals('application/json', $result->type());
    }

}
