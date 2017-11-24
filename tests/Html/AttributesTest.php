<?php

namespace Kirby\Html;

class AttributesTest extends TestCase
{

    public function testConstruct()
    {

        $attrs = new Attributes;
        $this->assertEquals([], $attrs->toArray());

        $attrs = new Attributes(['a' => 'a']);
        $this->assertEquals(['a' => 'a'], $attrs->toArray());

    }

    public function testContains()
    {

        $attrs = new Attributes(['a' => 'a']);
        $this->assertTrue($attrs->contains('a'));
        $this->assertFalse($attrs->contains('b'));

    }

    public function testGet()
    {

        // get one
        $attrs = new Attributes(['a' => 'a']);
        $attr  = $attrs->get('a');

        $this->assertInstanceOf('Kirby\\Html\\Attribute', $attr);
        $this->assertEquals('a', $attr->name());
        $this->assertEquals('a', $attr->value());

        // get all
        $attrs = new Attributes(['a' => 'a', 'b' => 'b']);
        $attr  = $attrs->get();

        $this->assertCount(2, $attr);

        $this->assertInstanceOf('Kirby\\Html\\Attribute', $attr['a']);
        $this->assertEquals('a', $attr['a']->name());
        $this->assertEquals('a', $attr['a']->value());

        $this->assertInstanceOf('Kirby\\Html\\Attribute', $attr['b']);
        $this->assertEquals('b', $attr['b']->name());
        $this->assertEquals('b', $attr['b']->value());

    }

    public function testSet()
    {

        // set one
        $attrs = new Attributes();
        $this->assertEquals([], $attrs->toArray());
        $attrs->set('testName', 'testValue');
        $this->assertEquals(['testname' => 'testValue'], $attrs->toArray());

        // set many
        $attrs = new Attributes();
        $this->assertEquals([], $attrs->toArray());
        $attrs->set([
            'a' => 'a',
            'b' => 'b'
        ]);
        $this->assertEquals([
            'a' => 'a',
            'b' => 'b'
        ], $attrs->toArray());

    }

    public function testRemove()
    {

        $attrs = new Attributes(['a' => 'a', 'b' => 'b']);

        $this->assertEquals(['a' => 'a', 'b' => 'b'], $attrs->toArray());
        $this->assertTrue($attrs->contains('a'));
        $this->assertTrue($attrs->contains('b'));

        $attrs->remove('a');

        $this->assertEquals(['b' => 'b'], $attrs->toArray());
        $this->assertFalse($attrs->contains('a'));
        $this->assertTrue($attrs->contains('b'));

        $attrs->remove('b');

        $this->assertFalse($attrs->contains('a'));
        $this->assertFalse($attrs->contains('b'));

        $this->assertEquals([], $attrs->toArray());

    }

    public function testToArray()
    {

        $arr   = [];
        $attrs = new Attributes();
        $this->assertEquals($arr, $attrs->toArray());

        $arr   = ['a' => 'a'];
        $attrs = new Attributes($arr);
        $this->assertEquals($arr, $attrs->toArray());

    }

    public function testToHtml()
    {

        $tests = [
            [
                'input'    => [],
                'expected' => ''
            ],
            [
                'input'    => ['a' => 'a', 'b' => 'b'],
                'expected' => 'a="a" b="b"'
            ],
            [
                'input'    => ['a' => 'a', 'b' => true],
                'expected' => 'a="a" b'
            ],
            [
                'input'    => ['a' => 'a', 'b' => ''],
                'expected' => 'a="a"'
            ],
            [
                'input'    => ['a' => 'a', 'b' => false],
                'expected' => 'a="a"'
            ],
        ];

        foreach($tests as $test) {
            $attrs = new Attributes($test['input']);
            $this->assertEquals($test['expected'], $attrs->toHtml());
            $this->assertEquals($test['expected'], $attrs->toString());
            $this->assertEquals($test['expected'], $attrs->__toString());
            $this->assertEquals($test['expected'], $attrs);
        }


    }

}
