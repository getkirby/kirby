<?php

namespace Kirby\Toolkit;

class CollectionMutatorTest extends TestCase
{
    public function testData()
    {
        $collection = new Collection();

        $this->assertEquals([], $collection->data());

        $collection->data([
            'three' => 'drei'
        ]);
        $this->assertEquals([
            'three' => 'drei'
        ], $collection->data());

        $collection->data([
            'one' => 'eins',
            'two' => 'zwei'
        ]);
        $this->assertEquals([
            'one' => 'eins',
            'two' => 'zwei'
        ], $collection->data());
    }

    public function testEmpty()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei'
        ]);

        $this->assertEquals([
            'one' => 'eins',
            'two' => 'zwei'
        ], $collection->data());

        $this->assertEquals([], $collection->empty()->data());
    }

    public function testSet()
    {
        $collection = new Collection();
        $this->assertEquals(null, $collection->one);
        $this->assertEquals(null, $collection->two);

        $collection->one = 'eins';
        $this->assertEquals('eins', $collection->one);

        $collection->set('two', 'zwei');
        $this->assertEquals('zwei', $collection->two);

        $collection->set([
            'three' => 'drei'
        ]);
        $this->assertEquals('drei', $collection->three);
    }

    public function testAppend()
    {
        $collection = new Collection([
            'one' => 'eins'
        ]);

        $this->assertEquals('eins', $collection->last());

        $collection->append('two', 'zwei');
        $this->assertEquals('zwei', $collection->last());
    }

    public function testPrepend()
    {
        $collection = new Collection([
            'one' => 'eins'
        ]);

        $this->assertEquals('eins', $collection->first());

        $collection->prepend('zero', 'null');
        $this->assertEquals('null', $collection->zero());
    }

    public function testExtend()
    {
        $collection = new Collection([
            'one' => 'eins'
        ]);

        $result = $collection->extend([
            'two' => 'zwei'
        ]);

        $this->assertEquals('eins', $result->one());
        $this->assertEquals('zwei', $result->two());
    }

    public function testRemove()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei'
        ]);

        $this->assertEquals('zwei', $collection->two());
        $collection->remove('two');
        $this->assertEquals(null, $collection->two());
    }

    public function testUnset()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei'
        ]);

        $this->assertEquals('zwei', $collection->two());
        unset($collection->two);
        $this->assertEquals(null, $collection->two());
    }

    public function testMap()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei'
        ]);

        $this->assertEquals('zwei', $collection->two());
        $collection->map(function ($item) {
            return $item . '-ish';
        });
        $this->assertEquals('zwei-ish', $collection->two());
    }

    public function testPluck()
    {
        $collection = new Collection([
            [
                'username' => 'homer',
            ],
            [
                'username' => 'marge',
            ]
        ]);

        $this->assertEquals(['homer', 'marge'], $collection->pluck('username'));
    }

    public function testPluckAndSplit()
    {
        $collection = new Collection([
            [
                'simpsons' => 'homer, marge',
            ],
            [
                'simpsons' => 'maggie, bart, lisa',
            ]
        ]);

        $expected = [
            'homer', 'marge', 'maggie', 'bart', 'lisa'
        ];

        $this->assertEquals($expected, $collection->pluck('simpsons', ', '));
    }

    public function testPluckUnique()
    {
        $collection = new Collection([
            [
                'user' => 'homer',
            ],
            [
                'user' => 'homer',
            ],
            [
                'user' => 'marge',
            ]
        ]);

        $expected = ['homer', 'marge'];

        $this->assertEquals($expected, $collection->pluck('user', null, true));
    }
}
