<?php

namespace Kirby\Toolkit;

class MockObject
{
    protected $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }
}

class MockObjectString extends MockObject
{
    public function __toString()
    {
        return (string)$this->value;
    }
}

class CollectionSorterTest extends TestCase
{
    public function testSort()
    {
        $collection = new Collection([
            [
                'name'  => 'Bastian',
                'role'  => 'developer',
                'color' => 'red'
            ],
            [
                'name' => 'Nico',
                'role' => 'developer',
                'color' => 'green'
            ],
            [
                'name' => 'nico',
                'role' => 'support',
                'color' => 'blue'
            ],
            [
                'name'  => 'Sonja',
                'role'  => 'support',
                'color' => 'red'
            ]
        ]);

        $sorted = $collection->sort('name', 'asc');
        $this->assertEquals('Bastian', $sorted->nth(0)['name']);
        $this->assertEquals('green', $sorted->nth(1)['color']);
        $this->assertEquals('blue', $sorted->nth(2)['color']);
        $this->assertEquals('Sonja', $sorted->nth(3)['name']);

        $sorted = $collection->sort('name', 'desc');
        $this->assertEquals('Bastian', $sorted->last()['name']);
        $this->assertEquals('Sonja', $sorted->first()['name']);

        $sorted = $collection->sort('name', 'asc', 'color', SORT_ASC);
        $this->assertEquals('blue', $sorted->nth(1)['color']);
        $this->assertEquals('green', $sorted->nth(2)['color']);

        $sorted = $collection->sort('name', 'asc', 'color', SORT_DESC);
        $this->assertEquals('green', $sorted->nth(1)['color']);
        $this->assertEquals('blue', $sorted->nth(2)['color']);
    }

    public function testSortFlags()
    {
        $collection = new Collection([
            ['name' => 'img12.png'],
            ['name' => 'img10.png'],
            ['name' => 'img2.png'],
            ['name' => 'img1.png']
        ]);

        $sorted = $collection->sort('name', 'asc', SORT_REGULAR);
        $this->assertEquals('img1.png', $sorted->nth(0)['name']);
        $this->assertEquals('img10.png', $sorted->nth(1)['name']);
        $this->assertEquals('img12.png', $sorted->nth(2)['name']);
        $this->assertEquals('img2.png', $sorted->nth(3)['name']);

        $sorted = $collection->sort('name', SORT_NATURAL);
        $this->assertEquals('img1.png', $sorted->nth(0)['name']);
        $this->assertEquals('img2.png', $sorted->nth(1)['name']);
        $this->assertEquals('img10.png', $sorted->nth(2)['name']);
        $this->assertEquals('img12.png', $sorted->nth(3)['name']);

        $sorted = $collection->sort('name', SORT_NATURAL, 'desc');
        $this->assertEquals('img12.png', $sorted->nth(0)['name']);
        $this->assertEquals('img10.png', $sorted->nth(1)['name']);
        $this->assertEquals('img2.png', $sorted->nth(2)['name']);
        $this->assertEquals('img1.png', $sorted->nth(3)['name']);
    }

    public function testSortCases()
    {
        $collection = new Collection([
            ['name' => 'a'],
            ['name' => 'c'],
            ['name' => 'A'],
            ['name' => 'b']
        ]);

        $sorted = $collection->sort('name', 'asc');
        $this->assertEquals('A', $sorted->nth(0)['name']);
        $this->assertEquals('a', $sorted->nth(1)['name']);
        $this->assertEquals('b', $sorted->nth(2)['name']);
        $this->assertEquals('c', $sorted->nth(3)['name']);
    }

    public function testSortIntegers()
    {
        $collection = new Collection([
            ['number' => 12],
            ['number' => 1],
            ['number' => 10],
            ['number' => 2]
        ]);

        $sorted = $collection->sort('number', 'asc');
        $this->assertEquals(1, $sorted->nth(0)['number']);
        $this->assertEquals(2, $sorted->nth(1)['number']);
        $this->assertEquals(10, $sorted->nth(2)['number']);
        $this->assertEquals(12, $sorted->nth(3)['number']);
    }

    public function testSortZeros()
    {
        $collection = new Collection([
            [
                'title'  => '1',
                'number' => 0
            ],
            [
                'title'  => '2',
                'number' => 0
            ],
            [
                'title'  => '3',
                'number' => 0
            ],
            [
                'title'  => '4',
                'number' => 0
            ]
        ]);

        $sorted = $collection->sort('number', 'asc');
        $this->assertEquals('1', $sorted->nth(0)['title']);
        $this->assertEquals('2', $sorted->nth(1)['title']);
        $this->assertEquals('3', $sorted->nth(2)['title']);
        $this->assertEquals('4', $sorted->nth(3)['title']);
    }

    public function testSortCallable()
    {
        $collection = new Collection([
            [
                'title' => 'Second Article',
                'date'  => '2019-10-01 10:04',
                'tags'  => 'test'
            ],
            [
                'title' => 'First Article',
                'date'  => '2018-31-12 00:00',
                'tags'  => 'test'
            ],
            [
                'title' => 'Third Article',
                'date'  => '01.10.2019 10:05',
                'tags'  => 'test'
            ]
        ]);

        $sorted = $collection->sort(function ($value) {
            $this->assertEquals('test', $value['tags']);

            return strtotime($value['date']);
        }, 'asc');
        $this->assertEquals('First Article', $sorted->nth(0)['title']);
        $this->assertEquals('Second Article', $sorted->nth(1)['title']);
        $this->assertEquals('Third Article', $sorted->nth(2)['title']);

        $sorted = $collection->sort(function ($value) {
            $this->assertEquals('test', $value['tags']);

            return strtotime($value['date']);
        }, 'desc');
        $this->assertEquals('Third Article', $sorted->nth(0)['title']);
        $this->assertEquals('Second Article', $sorted->nth(1)['title']);
        $this->assertEquals('First Article', $sorted->nth(2)['title']);
    }

    public function testSortEmpty()
    {
        $collection = new Collection();
        $this->assertEquals($collection, $collection->sort());
    }

    public function testSortObjects()
    {
        $bastian = new MockObjectString('Bastian');
        $nico    = new MockObjectString('Nico');
        $sonja   = new MockObjectString('Sonja');

        $collection = new Collection([
            ['name' => $nico],
            ['name' => $bastian],
            ['name' => $sonja]
        ]);

        $sorted = $collection->sort('name', 'asc');
        $this->assertEquals($bastian, $sorted->nth(0)['name']);
        $this->assertEquals($nico, $sorted->nth(1)['name']);
        $this->assertEquals($sonja, $sorted->nth(2)['name']);
    }

    public function testSortNoRecursiveDependencyError()
    {
        // arrays; shouldn't be a problem
        $collection = new Collection([
            ['name' => 'img1.png'],
            ['name' => 'img2.png'],
            ['name' => 'img1.png']
        ]);

        $sorted = $collection->sort('name', 'asc');
        $this->assertEquals('img1.png', $sorted->nth(0)['name']);
        $this->assertEquals('img1.png', $sorted->nth(1)['name']);
        $this->assertEquals('img2.png', $sorted->nth(2)['name']);

        // objects with a __toString() method
        $bastian = new MockObjectString('Bastian');
        $nico    = new MockObjectString('Nico');
        $sonja   = new MockObjectString('Sonja');

        $collection = new Collection([
            'nico'     => $nico,
            'bastian1' => $bastian,
            'sonja'    => $sonja,
            'bastian2' => $bastian
        ]);
        $sorted = $collection->sort('value', 'asc');
        $this->assertEquals($bastian, $sorted->nth(0));
        $this->assertEquals($bastian, $sorted->nth(1));
        $this->assertEquals($nico, $sorted->nth(2));
        $this->assertEquals($sonja, $sorted->nth(3));

        // objects without a __toString() method
        $bastian = new MockObject('Bastian');
        $nico    = new MockObject('Nico');
        $sonja   = new MockObject('Sonja');

        $collection = new Collection([
            'nico'     => $nico,
            'bastian1' => $bastian,
            'sonja'    => $sonja,
            'bastian2' => $bastian
        ]);
        $sorted = $collection->sort('value', 'asc');
        $this->assertEquals($bastian, $sorted->nth(0));
        $this->assertEquals($bastian, $sorted->nth(1));
        $this->assertEquals($nico, $sorted->nth(2));
        $this->assertEquals($sonja, $sorted->nth(3));
    }

    public function testFlip()
    {
        $collection = new Collection([
            ['name' => 'img12.png'],
            ['name' => 'img10.png'],
            ['name' => 'img2.png']
        ]);

        $this->assertEquals('img12.png', $collection->nth(0)['name']);
        $this->assertEquals('img10.png', $collection->nth(1)['name']);
        $this->assertEquals('img2.png', $collection->nth(2)['name']);

        $flipped = $collection->flip();
        $this->assertEquals('img2.png', $flipped->nth(0)['name']);
        $this->assertEquals('img10.png', $flipped->nth(1)['name']);
        $this->assertEquals('img12.png', $flipped->nth(2)['name']);
    }

    public function testShuffle()
    {
        $collection = new Collection([
            ['name' => 'img12.png'],
            ['name' => 'img10.png'],
            ['name' => 'img2.png']
        ]);

        $shuffled = $collection->shuffle();
        $this->assertEquals(3, $shuffled->count());
    }
}
