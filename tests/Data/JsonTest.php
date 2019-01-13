<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testEncodeDecode()
    {
        $array = [
            'name'     => 'Homer',
            'children' => ['Lisa', 'Bart', 'Maggie']
        ];

        $data = Json::encode($array);
        $this->assertEquals('{"name":"Homer","children":["Lisa","Bart","Maggie"]}', $data);

        $result = Json::decode($data);
        $this->assertEquals($array, $result);
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage JSON string is invalid
     */
    public function testDecodeCorrupted()
    {
        Json::decode('some gibberish');
    }
}
