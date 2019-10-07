<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Data\Txt
 */
class TxtTest extends TestCase
{
    const FIXTURES = __DIR__ . '/fixtures';

    /**
     * @covers ::encode
     * @covers ::encodeValue
     * @covers ::encodeResult
     * @covers ::decode
     */
    public function testEncodeDecode()
    {
        $array = [
            'title' => 'Title',
            'text'  => 'Text'
        ];

        $data = Txt::encode($array);
        $this->assertSame(
            "Title: Title\n\n----\n\nText: Text",
            $data
        );

        $result = Txt::decode($data);
        $this->assertSame($array, $result);
    }

    /**
     * @covers ::encode
     * @covers ::encodeValue
     * @covers ::encodeResult
     */
    public function testEncodeMissingValues()
    {
        $array = [
            'title' => 'Title',
            'text'  => null,
            ''      => 'text',
            'field' => 'content'
        ];

        $data = Txt::encode($array);
        $this->assertSame(
            "Title: Title\n\n----\n\nField: content",
            $data
        );
    }

    /**
     * @covers ::encode
     * @covers ::encodeValue
     * @covers ::encodeResult
     */
    public function testEncodeMultiline()
    {
        $array = [
            'title' => 'Title',
            'text'  => "Text\nText"
        ];

        $data = Txt::encode($array);
        $this->assertSame(
            "Title: Title\n\n----\n\nText:\n\nText\nText",
            $data
        );
    }

    /**
     * @covers ::encode
     * @covers ::encodeValue
     * @covers ::encodeResult
     */
    public function testEncodeDecodeDivider()
    {
        $array = [
            'title' => 'Title',
            'text'  => "----\n----\nText\n\n----Field:\nValue\n----  \n----"
        ];

        $data = Txt::encode($array);
        $this->assertSame(
            "Title: Title\n\n----\n\nText:\n\n\\----\n\\----\n" .
            "Text\n\n\\----Field:\nValue\n\\----  \n\\----",
            $data
        );

        $this->assertSame($array, Txt::decode($data));
    }

    /**
     * @covers ::encode
     * @covers ::encodeValue
     * @covers ::encodeResult
     */
    public function testEncodeArray()
    {
        $array = [
            'title' => 'Title',
            'text'  => ['a', 'b', 'c']
        ];

        $data = Txt::encode($array);
        $this->assertSame(file_get_contents(static::FIXTURES . '/test.txt'), $data);
    }

    /**
     * @covers ::encode
     * @covers ::encodeValue
     * @covers ::encodeResult
     */
    public function testEncodeFloat()
    {
        $data = Txt::encode([
            'number' => (float)3.2
        ]);

        $this->assertSame('Number: 3.2', $data);
    }

    /**
     * @covers ::encode
     * @covers ::encodeValue
     * @covers ::encodeResult
     */
    public function testEncodeFloatWithLocaleSetting()
    {
        $currentLocale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, 'de_DE');

        $data = Txt::encode([
            'number' => (float)3.2
        ]);

        $this->assertSame('Number: 3.2', $data);

        setlocale(LC_ALL, $currentLocale);
    }

    /**
     * @covers ::decode
     */
    public function testDecodeFile()
    {
        $array = [
            'title_with_spaces' => 'Title',
            'text_with_dashes'  => 'Text'
        ];

        $data = Txt::decode(file_get_contents(static::FIXTURES . '/decode.txt'));
        $this->assertSame($array, $data);
    }
}
