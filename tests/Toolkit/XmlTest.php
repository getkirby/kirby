<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    protected $string;

    protected function setUp(): void
    {
        $this->string = 'Süper Önencœded ßtring';
    }

    public function testCreateParse()
    {
        $xml = Xml::create($data = [
            'simpson' => [
                [
                    'name' => 'Homer',
                    '@attributes' => [
                        'type' => 'father'
                    ]
                ],
                [
                    'name' => 'Marge',
                    '@attributes' => [
                        'type' => 'mother'
                    ]
                ]
            ]
        ], 'simpsons');

        $output = Xml::parse($xml);

        $this->assertEquals($output, $data);
    }

    public function testEncodeDecode()
    {
        $expected = 'S&#252;per &#214;nenc&#339;ded &#223;tring';

        $this->assertEquals($expected, Xml::encode($this->string));
        $this->assertEquals($this->string, Xml::decode($expected));
    }

    public function testEntities()
    {
        $this->assertTrue(is_array(Xml::entities()));
    }

    public function testTag()
    {
        $tag = Xml::tag('name', 'content');
        $this->assertEquals('<name>content</name>', $tag);
    }

    public function testTagWithAttributes()
    {
        $tag = Xml::tag('name', 'content', ['foo' => 'bar']);
        $this->assertEquals('<name foo="bar">content</name>', $tag);
    }

    public function testTagWithCdata()
    {
        $tag = Xml::tag('name', $this->string, ['foo' => 'bar']);
        $this->assertEquals('<name foo="bar"><![CDATA[' . Xml::encode($this->string) . ']]></name>', $tag);
    }

    public function valueProvider()
    {
        return [
            [1, 1],
            [true, 'true'],
            [false, 'false'],
            [null, null],
            ['', null],
            ['<![CDATA[test]]>', '<![CDATA[test]]>'],
            ['test', 'test'],
            ['töst', '<![CDATA[t&#246;st]]>']
        ];
    }

    /**
     * @dataProvider valueProvider
     */
    public function testValue($input, $expected)
    {
        $this->assertEquals($expected, Xml::value($input));
    }
}
