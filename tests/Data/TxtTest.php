<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;

class TxtTest extends TestCase
{
    const FIXTURES = __DIR__ . '/fixtures';

    public function testEncodeDecode()
    {
        $array = [
            'title' => 'Title',
            'text'  => 'Text'
        ];

        $data = Txt::encode($array);
        $this->assertEquals(
            "Title: Title\n\n----\n\nText: Text",
            $data
        );

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
        $this->assertEquals(file_get_contents(static::FIXTURES . '/test.txt'), $data);
    }

    public function testDecodeFile()
    {
        $array = [
            'title' => 'Title'
        ];

        $data = Txt::decode(file_get_contents(static::FIXTURES . '/emptyfield.txt'));
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
