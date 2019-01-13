<?php

namespace Kirby\Toolkit;

class CollectionConverterTest extends TestCase
{
    public function testToArray()
    {
        $array = [
            'one'   => 'eins',
            'two'   => 'zwei'
        ];
        $collection = new Collection($array);
        $this->assertEquals($array, $collection->toArray());
    }

    public function testToArrayMap()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei'
        ]);
        $this->assertEquals([
            'one'   => 'einsy',
            'two'   => 'zweiy'
        ], $collection->toArray(function ($item) {
            return $item . 'y';
        }));
    }

    public function testToJson()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei'
        ]);
        $this->assertEquals('{"one":"eins","two":"zwei"}', $collection->toJson());
    }

    public function testToString()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei'
        ]);
        $string = 'one<br />two';
        $this->assertEquals($string, $collection->toString());
        $this->assertEquals($string, (string)$collection);
    }
}
