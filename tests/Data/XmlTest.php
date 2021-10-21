<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Data\Xml
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

        $this->assertSame([], Xml::decode(null));
        $this->assertSame([], Xml::decode(''));
        $this->assertSame(['this is' => 'an array'], Xml::decode(['this is' => 'an array']));
    }

    /**
     * @covers ::decode
     */
    public function testDecodeInvalid1()
    {
        // pass invalid object
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid XML data; please pass a string');
        Xml::decode(new \stdClass());
    }

    /**
     * @covers ::decode
     */
    public function testDecodeInvalid2()
    {
        // pass invalid int
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid XML data; please pass a string');
        Xml::decode(1);
    }

    /**
     * @covers ::encode
     */
    public function testEncodeScalar()
    {
        $expected = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<data>test</data>';
        $this->assertSame($expected, Xml::encode('test'));
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
