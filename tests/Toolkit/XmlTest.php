<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{

    protected $string;

    protected function setUp()
    {
        $this->string = 'Süper Önencœded ßtring';
    }

    public function testEncodeDecode()
    {
        $expected = 'S&#252;per &#214;nenc&#339;ded &#223;tring';

        $this->assertEquals($expected, Xml::encode($this->string));
        $this->assertEquals($this->string, Xml::decode($expected));
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
        $this->assertEquals('<name foo="bar"><![CDATA[' . $this->string . ']]></name>', $tag);
    }

}
