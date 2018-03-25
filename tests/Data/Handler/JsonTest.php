<?php

namespace Kirby\Data\Handler;

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

    public function corruptedDecode()
    {
        $data = 'some gibberish';
        $this->assertNull(Json::decode($data));
    }
}
