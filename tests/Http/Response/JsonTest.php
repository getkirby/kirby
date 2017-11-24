<?php

namespace Kirby\Http\Response;

use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{

    public function testType()
    {
        $response = new Json();
        $this->assertEquals('application/json', $response->type());
    }

    public function testBodyInConstructor()
    {
        // with string
        $array    = ['test' => 'test'];
        $json     = json_encode($array);
        $response = new Json($json);
        $this->assertEquals($json, $response->body());

        // with array
        $response = new Json($array);
        $this->assertEquals($json, $response->body());
    }

    public function testBodySetter()
    {
        $response = new Json;
        $array    = ['test' => 'test'];
        $json     = json_encode($array);

        // with string
        $this->assertEquals($json, $response->body($json));
        $this->assertEquals($json, $response->body());

        // with array
        $this->assertEquals($json, $response->body($array));
        $this->assertEquals($json, $response->body());
    }
}
