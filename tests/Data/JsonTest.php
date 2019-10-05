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
        $this->assertSame('{"name":"Homer","children":["Lisa","Bart","Maggie"]}', $data);

        $result = Json::decode($data);
        $this->assertSame($array, $result);
    }

    /**
     * @covers ::encode
     */
    public function testEncodeUnicode()
    {
        $string  = 'здравей';
        $this->assertSame('"' . $string . '"', Json::encode($string));
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
