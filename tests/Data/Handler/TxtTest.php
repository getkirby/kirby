<?php

namespace Kirby\Data\Handler;

use PHPUnit\Framework\TestCase;

class TxtTest extends TestCase
{

    public function testEncodeDecode()
    {
        $array = [
            'title' => 'Title',
            'text'  => 'Text'
        ];

        $data   = Txt::encode($array);
        $result = Txt::decode($data);

        $this->assertEquals($array, $result);
    }

    public function testEncodeArray()
    {
        $array = [
            'title' => 'Title',
            'text'  => ['a', 'b', 'c']
        ];

        $data = Txt::encode($array);
        $this->assertEquals(file_get_contents(dirname(__DIR__) . '/fixtures/test.txt'), $data);
    }

    public function testDecodeFile()
    {
        $array = [
            'title' => 'Title'
        ];

        $data = Txt::decode(file_get_contents(dirname(__DIR__) . '/fixtures/emptyfield.txt'));
        $this->assertEquals($array, $data);
    }

    public function testEncodeMissingValues()
    {
        $array = [
            'title' => 'Title',
            'text'  => null,
            ''      => 'text',
            'field' => 'content'
        ];

        $data   = Txt::encode($array);
        $result = Txt::decode($data);

        $this->assertEquals([
            'title' => 'Title',
            'field' => 'content'
        ], $result);
    }
}
