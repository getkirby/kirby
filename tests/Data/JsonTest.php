<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Data\Json
 */
class JsonTest extends TestCase
{
    /**
     * @covers ::encode
     * @covers ::decode
     */
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
     * @covers ::decode
     */
    public function testDecodeCorrupted1()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('JSON string is invalid');

        Json::decode('some gibberish');
    }

    /**
     * @covers ::decode
     */
    public function testDecodeCorrupted2()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('JSON string is invalid');

        Json::decode('true');
    }
}
