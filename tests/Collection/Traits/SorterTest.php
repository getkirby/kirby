<?php

namespace Kirby\Collection\Traits;

use Kirby\Collection\Collection;

use PHPUnit\Framework\TestCase;

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

        $sorted = $collection->sortBy('name', 'asc', 'color', 'asc');
        $this->assertEquals('blue', $sorted->nth(1)['color']);
        $this->assertEquals('green', $sorted->nth(2)['color']);
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
    }

    public function testSortByEmpty()
    {
        $collection = new Collection();
        $this->assertEquals($collection, $collection->sortBy());
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
