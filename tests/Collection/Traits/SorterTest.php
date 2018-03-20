<?php

namespace Kirby\Collection\Traits;

use Kirby\Collection\Collection;

use PHPUnit\Framework\TestCase;

class MockObject
{

    protected $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString() {
        return (string)$this->value;
    }

}

class SorterTest extends TestCase
{

    public function testSortBy()
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
                'name' => 'Nico',
                'role' => 'support',
                'color' => 'blue'
            ],
            [
                'name'  => 'Sonja',
                'role'  => 'support',
                'color' => 'red'
            ]
        ]);

        $sorted = $collection->sortBy('name', 'asc');
        $this->assertEquals('Bastian', $sorted->nth(0)['name']);
        $this->assertEquals('green', $sorted->nth(1)['color']);
        $this->assertEquals('blue', $sorted->nth(2)['color']);
        $this->assertEquals('Sonja', $sorted->nth(3)['name']);

        $sorted = $collection->sortBy('name', 'desc');
        $this->assertEquals('Bastian', $sorted->last()['name']);
        $this->assertEquals('Sonja', $sorted->first()['name']);

        $sorted = $collection->sortBy('name', 'asc', 'color', SORT_ASC);
        $this->assertEquals('blue', $sorted->nth(1)['color']);
        $this->assertEquals('green', $sorted->nth(2)['color']);

        $sorted = $collection->sortBy('name', 'asc', 'color', SORT_DESC);
        $this->assertEquals('green', $sorted->nth(1)['color']);
        $this->assertEquals('blue', $sorted->nth(2)['color']);
    }

    public function testSortByNatural()
    {
        $collection = new Collection([
            ['name' => 'img12.png'],
            ['name' => 'img10.png'],
            ['name' => 'img2.png'],
            ['name' => 'img1.png']
        ]);

        $sorted = $collection->sortBy('name', 'asc');
        $this->assertEquals('img1.png', $sorted->nth(0)['name']);
        $this->assertEquals('img10.png', $sorted->nth(1)['name']);
        $this->assertEquals('img12.png', $sorted->nth(2)['name']);
        $this->assertEquals('img2.png', $sorted->nth(3)['name']);

        $sorted = $collection->sortBy('name', SORT_NATURAL);
        $this->assertEquals('img1.png', $sorted->nth(0)['name']);
        $this->assertEquals('img2.png', $sorted->nth(1)['name']);
        $this->assertEquals('img10.png', $sorted->nth(2)['name']);
        $this->assertEquals('img12.png', $sorted->nth(3)['name']);

        $sorted = $collection->sortBy('name', SORT_NATURAL, 'desc');
        $this->assertEquals('img12.png', $sorted->nth(0)['name']);
        $this->assertEquals('img10.png', $sorted->nth(1)['name']);
        $this->assertEquals('img2.png', $sorted->nth(2)['name']);
        $this->assertEquals('img1.png', $sorted->nth(3)['name']);
    }

    public function testSortIntegers()
    {
        $collection = new Collection([
            ['number' => 12],
            ['number' => 1],
            ['number' => 10],
            ['number' => 2]
        ]);

        $sorted = $collection->sortBy('number', 'asc');
        $this->assertEquals(1, $sorted->nth(0)['number']);
        $this->assertEquals(2, $sorted->nth(1)['number']);
        $this->assertEquals(10, $sorted->nth(2)['number']);
        $this->assertEquals(12, $sorted->nth(3)['number']);
    }

    public function testSortByEmpty()
    {
        $collection = new Collection();
        $this->assertEquals($collection, $collection->sortBy());
    }

    public function testSortObjects()
    {
        $bastian = new MockObject('Bastian');
        $nico    = new MockObject('Nico');
        $sonja   = new MockObject('Sonja');

        $collection = new Collection([
            ['name' => $nico],
            ['name' => $bastian],
            ['name' => $sonja]
        ]);

        $sorted = $collection->sortBy('name', 'asc');
        $this->assertEquals($bastian, $sorted->nth(0)['name']);
        $this->assertEquals($nico, $sorted->nth(1)['name']);
        $this->assertEquals($sonja, $sorted->nth(2)['name']);
    }

    public function testFlip()
    {
        $collection = new Collection([
            ['name' => 'img12.png'],
            ['name' => 'img10.png'],
            ['name' => 'img2.png']
        ]);

        $sorted = $collection->sortBy('name', 'asc');
        $this->assertEquals('img12.png', $collection->first(0)['name']);
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
