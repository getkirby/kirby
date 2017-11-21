<?php

namespace Kirby\Html;

class AttributeTest extends TestCase
{

    public function testConstruct()
    {

        $attr = new Attribute('href', 'https://getkirby.com');
        $this->assertEquals('href', $attr->name());
        $this->assertEquals('https://getkirby.com', $attr->value());

    }

    public function testName()
    {

        // setter
        $attr   = new Attribute('href', '');
        $result = $attr->name('rel');

        $this->assertInstanceOf('Kirby\\Html\\Attribute', $attr);
        $this->assertEquals('rel', $attr->name());
        $this->assertEquals('rel', $result->name());

        // uppercase
        $attr = new Attribute('HREF');
        $this->assertEquals('href', $attr->name());

        // getter
        $attr = new Attribute('href');
        $this->assertEquals('href', $attr->name());

    }

    public function testValue()
    {

        // setter
        $attr = new Attribute('href');

        $tests = [
            null   => '',
            'test' => 'test',
            false  => false,
            true   => true,
            1      => '1',
            0      => '0',
            ''     => '',
        ];

        foreach($tests as $value => $expected) {

            $result = $attr->value($value);

            $this->assertInstanceOf('Kirby\\Html\\Attribute', $attr);
            $this->assertEquals($expected, $attr->value());
            $this->assertEquals($expected, $result->value());
            $this->assertEquals($expected, $result->toString());
            $this->assertEquals($expected, $result->__toString());
            $this->assertEquals($expected, $result);

        }

    }

    /**
     * @expectedException         Exception
     * @expectedExceptionMessage  Invalid Attribute value type
     */
    public function testInvalidValue()
    {
        new Attribute('href', new \stdClass);
    }

    public function testToArray()
    {
        $attr = new Attribute('href', 'https://getkirby.com');
        $this->assertEquals(['href' => 'https://getkirby.com'], $attr->toArray());
    }

    public function testToHtml()
    {

        $tests = [
            [
                'name'     => '',
                'value'    => '',
                'expected' => ''
            ],
            [
                'name'     => 'href',
                'value'    => 'https://getkirby.com',
                'expected' => 'href="https://getkirby.com"'
            ],
            [
                'name'     => 'value',
                'value'    => '0',
                'expected' => 'value="0"'
            ],
            [
                'name'     => 'value',
                'value'    => '1',
                'expected' => 'value="1"'
            ],
            [
                'name'     => 'hidden',
                'value'    => true,
                'expected' => 'hidden'
            ],
            [
                'name'     => 'hidden',
                'value'    => false,
                'expected' => ''
            ],
            [
                'name'     => 'hidden',
                'value'    => '',
                'expected' => ''
            ],
            [
                'name'     => 'alt',
                'value'    => ' ',
                'expected' => 'alt=""'
            ]
        ];

        foreach($tests as $i => $test) {
            $attr = new Attribute($test['name'], $test['value']);
            $this->assertEquals($test['expected'], $attr->toHtml(), 'Check: ' . $i);
        }

    }


}
