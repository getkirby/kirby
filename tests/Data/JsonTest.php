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
     * @covers ::encode
     */
    public function testEncodeUnicode()
    {
        $string  = 'здравей';
        $encoded = '\u0437\u0434\u0440\u0430\u0432\u0435\u0439';
        $this->assertEquals('"' . $encoded . '"', Json::encode($string));
        $this->assertEquals('"' . $string . '"', Json::encode($string, JSON_UNESCAPED_UNICODE));
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
