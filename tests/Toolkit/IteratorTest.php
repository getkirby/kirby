<?php

namespace Kirby\Toolkit;

class IteratorTest extends TestCase
{
    public function testKey()
    {
        $iterator = new Iterator([
            'one' => 'eins',
            'two' => 'zwei',
        ]);

        $this->assertEquals('one', $iterator->key());
    }

    public function testKeys()
    {
        $iterator = new Iterator([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei'
        ]);

        $this->assertEquals([
            'one',
            'two',
            'three'
        ], $iterator->keys());
    }

    public function testCurrent()
    {
        $iterator = new Iterator([
            'one' => 'eins',
            'two' => 'zwei',
        ]);

        $this->assertEquals('eins', $iterator->current());
    }

    public function testPrevNext()
    {
        $iterator = new Iterator([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei'
        ]);

        $this->assertEquals('eins', $iterator->current());

        $iterator->next();
        $this->assertEquals('zwei', $iterator->current());

        $iterator->next();
        $this->assertEquals('drei', $iterator->current());

        $iterator->prev();
        $this->assertEquals('zwei', $iterator->current());

        $iterator->prev();
        $this->assertEquals('eins', $iterator->current());
    }

    public function testRewind()
    {
        $iterator = new Iterator([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei'
        ]);

        $iterator->next();
        $iterator->next();
        $this->assertEquals('drei', $iterator->current());

        $iterator->rewind();
        $this->assertEquals('eins', $iterator->current());
    }

    public function testValid()
    {
        $iterator = new Iterator([]);
        $this->assertFalse($iterator->valid());

        $iterator = new Iterator(['one' => 'eins']);
        $this->assertTrue($iterator->valid());
    }

    public function testCount()
    {
        $iterator = new Iterator([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei'
        ]);
        $this->assertEquals(3, $iterator->count());

        $iterator = new Iterator(['one' => 'eins']);
        $this->assertEquals(1, $iterator->count());

        $iterator = new Iterator([]);
        $this->assertEquals(0, $iterator->count());
    }

    public function testIndexOf()
    {
        $iterator = new Iterator([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei'
        ]);

        $this->assertEquals(0, $iterator->indexOf('eins'));
        $this->assertEquals(1, $iterator->indexOf('zwei'));
        $this->assertEquals(2, $iterator->indexOf('drei'));
    }

    public function testKeyOf()
    {
        $iterator = new Iterator([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei'
        ]);

        $this->assertEquals('one', $iterator->keyOf('eins'));
        $this->assertEquals('two', $iterator->keyOf('zwei'));
        $this->assertEquals('three', $iterator->keyOf('drei'));
    }

    public function testHas()
    {
        $iterator = new Iterator([
            'one'   => 'eins',
            'two'   => 'zwei'
        ]);

        $this->assertTrue($iterator->has('one'));
        $this->assertTrue($iterator->has('two'));
        $this->assertFalse($iterator->has('three'));
    }

    public function testIsset()
    {
        $iterator = new Iterator([
            'one'   => 'eins',
            'two'   => 'zwei'
        ]);

        $this->assertTrue(isset($iterator->one));
        $this->assertTrue(isset($iterator->two));
        $this->assertFalse(isset($iterator->three));
    }

    public function testDebuginfo()
    {
        $array = [
            'one'   => 'eins',
            'two'   => 'zwei'
        ];

        $iterator = new Iterator($array);
        $this->assertEquals($array, $iterator->__debugInfo());
    }
}
