<?php

namespace Kirby\Toolkit;

class CollectionFilterTest extends TestCase
{

    public function testFilterArray()
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
                'name'  => 'Sonja',
                'role'  => 'support',
                'color' => 'red'
            ]
        ]);

        $result = new Collection([
            [
                'name'  => 'Bastian',
                'role'  => 'developer',
                'color' => 'red'
            ]
        ]);

        $this->assertEquals($result, $collection->filter([
            ['role', '==', 'developer'],
            ['color', '==', 'red']
        ]));
    }

    public function testFilterClosure()
    {
        $collection = new Collection([
            [
                'name'  => 'Bastian',
                'role'  => 'founder'
            ],
            [
                'name' => 'Nico',
                'role' => 'developer'
            ]
        ]);

        $result = new Collection([
            [
                'name'  => 'Bastian',
                'role'  => 'founder'
            ]
        ]);

        $this->assertEquals($result, $collection->filter(function ($item) {
            return $item['role'] === 'founder';
        }));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage The filter method needs either an array of filterBy rules or a closure function to be passed as parameter.
     */
    public function testFilterException()
    {
        $collection = new Collection([
            'one'   => 'eins'
        ]);

        $collection->filter('one');
    }

    public function testFilterByEquals()
    {
        $collection = new Collection([
            [
                'name'  => 'Bastian',
                'role'  => 'founder'
            ],
            [
                'name' => 'Nico',
                'role' => 'developer'
            ]
        ]);

        $result = new Collection([
            [
                'name'  => 'Bastian',
                'role'  => 'founder'
            ]
        ]);

        $this->assertEquals($result, $collection->filterBy('role', 'founder'));
    }

    public function testFilterByLess()
    {
        $collection = new Collection([
            [
                'name'   => 'Bastian',
                'number' => 1
            ],
            [
                'name'   => 'Nico',
                'number' => 5
            ]
        ]);

        $result = new Collection([
            [
                'name'   => 'Bastian',
                'number' => 1
            ]
        ]);

        $this->assertEquals($result, $collection->filterBy('number', '<', 3));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Missing filter for operator: $%
     */
    public function testFilterByUnknown()
    {
        $collection = new Collection(['one' => 'eins']);
        $collection->filterBy('one', '$%', 'fail');
    }

    public function testNot()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei'
        ]);

        $result = new Collection([
            'two' => 'zwei',
        ]);

        $this->assertEquals($result, $collection->not('one', 'three'));
    }

}
