<?php

namespace Kirby\Html;

class ClassListTest extends TestCase
{

    public function testConstruct()
    {

        // without anything
        $classes = new ClassList;
        $this->assertEquals([], $classes->toArray());

        // with an array of classes
        $classes = new ClassList(['link', 'link-primary']);
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());

        // with a list of classes
        $classes = new ClassList('link', 'link-primary');
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());

    }

    public function testContains()
    {
        $classes = new ClassList('link');
        $this->assertTrue($classes->contains('link'));
        $this->assertFalse($classes->contains('link-primary'));
    }

    public function testAdd()
    {

        // single
        $classes = new ClassList;
        $classes->add('link');
        $this->assertEquals(['link'], $classes->toArray());

        // multiple
        $classes = new ClassList;
        $classes->add('link', 'link-primary');
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());

        // multiple as array
        $classes = new ClassList;
        $classes->add(['link', 'link-primary']);
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());

        // multiple with spaces
        $classes = new ClassList;
        $classes->add('link link-primary');
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());

        // mixed
        $classes = new ClassList;
        $classes->add('link', 'btn btn-primary', ['large', 'highlight']);
        $this->assertEquals(['link', 'btn', 'btn-primary', 'large', 'highlight'], $classes->toArray());

        // with spaces
        $classes = new ClassList;
        $classes->add(' untrimmed ');
        $this->assertEquals(['untrimmed'], $classes->toArray());

    }

    public function testRemove()
    {

        // single
        $classes = new ClassList('link', 'link-primary');
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());
        $classes->remove('link-primary');
        $this->assertEquals(['link'], $classes->toArray());

        // multiple
        $classes = new ClassList('link', 'link-primary');
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());
        $classes->remove('link', 'link-primary');
        $this->assertEquals([], $classes->toArray());

        // multiple as array
        $classes = new ClassList('link', 'link-primary');
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());
        $classes->remove(['link', 'link-primary']);
        $this->assertEquals([], $classes->toArray());

        // multiple with spaces
        $classes = new ClassList('link', 'link-primary');
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());
        $classes->remove('link link-primary');
        $this->assertEquals([], $classes->toArray());

        // mixed
        $classes = new ClassList('link', 'btn', 'btn-primary', 'large', 'highlight');
        $this->assertEquals(['link', 'btn', 'btn-primary', 'large', 'highlight'], $classes->toArray());
        $classes->remove('link', 'btn btn-primary', ['large', 'highlight']);
        $this->assertEquals([], $classes->toArray());

        // with spaces
        $classes = new ClassList('link');
        $this->assertEquals(['link'], $classes->toArray());
        $classes->remove(' link ');
        $this->assertEquals([], $classes->toArray());

    }

    public function testToggle()
    {

        $classes = new ClassList();
        $this->assertEquals([], $classes->toArray());

        $classes->toggle('link');
        $this->assertEquals(['link'], $classes->toArray());

        $classes->toggle('link');
        $this->assertEquals([], $classes->toArray());

    }

    public function testToArray()
    {

        // empty list
        $classes = new ClassList();
        $this->assertEquals([], $classes->toArray());

        // filled list
        $classes = new ClassList('link', 'link-primary');
        $this->assertEquals(['link', 'link-primary'], $classes->toArray());

    }

    public function testToString()
    {

        $tests = [
            [
                'input'    => [],
                'expected' => ''
            ],
            [
                'input'    => ['link', 'link-primary'],
                'expected' => 'link link-primary'
            ],
            [
                'input'    => [['link', 'link-primary']],
                'expected' => 'link link-primary'
            ],
            [
                'input'    => ['link', ['link-primary']],
                'expected' => 'link link-primary'
            ],
            [
                'input'    => ['link link-primary'],
                'expected' => 'link link-primary'
            ],
            [
                'input'    => [' link  link-primary '],
                'expected' => 'link link-primary'
            ]
        ];

        foreach($tests as $test) {
            $classes = new ClassList(...$test['input']);
            $this->assertEquals($test['expected'], $classes->toString());
            $this->assertEquals($test['expected'], $classes->__toString());
            $this->assertEquals($test['expected'], $classes);
        }

    }


}
