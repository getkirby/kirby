<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Data\Xml
 */
class XmlTest extends TestCase
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

        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<data>\n  <name>Homer</name>\n  " .
                    "<children>Lisa</children>\n  <children>Bart</children>\n  <children>Maggie</children>\n</data>";

        $data = Xml::encode($array);
        $this->assertSame($expected, $data);

        $result = Xml::decode($data);
        $this->assertSame($array, $result);

        // with a custom root name
        $expected = str_replace('data>', 'custom>', $expected);
        $array = [
            '@name'    => 'custom',
            'name'     => 'Homer',
            'children' => ['Lisa', 'Bart', 'Maggie']
        ];
        $result = Xml::decode($expected);
        $this->assertSame($array, $result);

        // pass invalid object
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid XML data. Please pass a string');
        Xml::decode(new \stdClass());

        // pass invalid int
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid XML data. Please pass a string');
        Xml::decode(1);
    }

    /**
     * @covers ::decode
     */
    public function testDecodeCorrupted()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('XML string is invalid');

        Xml::decode('some gibberish');
    }
}
