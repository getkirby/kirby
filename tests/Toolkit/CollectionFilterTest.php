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

    public function testFilterException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The filter method needs either an array of filterBy rules or a closure function to be passed as parameter.');

        $collection = new Collection([
            'one'   => 'eins'
        ]);

        $collection->filter('one');
    }

    public function filterDataProvider()
    {
        return [

            // equals
            [
                'attributes' => ['a' => 'a', 'b' => 'b', 'c' => 'a'],
                'operator'   =>  '==',
                'test'       => 'a',
                'expected'   => ['a', 'c'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'a, b', 'b' => 'b, c', 'c' => 'c, d'],
                'operator'   =>  '==',
                'test'       => 'b',
                'expected'   => ['a', 'b'],
                'split'      => ','
            ],

            // not equals
            [
                'attributes' => ['a' => 'a', 'b' => 'b', 'c' => 'a'],
                'operator'   =>  '!=',
                'test'       => 'a',
                'expected'   => ['b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'a, b', 'b' => 'b, c', 'c' => 'c, d'],
                'operator'   =>  '!=',
                'test'       => 'b',
                'expected'   => ['c'],
                'split'      => ','
            ],

            // in
            [
                'attributes' => ['a' => 'a', 'b' => 'b', 'c' => 'c'],
                'operator'   =>  'in',
                'test'       => ['a', 'c'],
                'expected'   => ['a', 'c'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'aa, ab', 'b' => 'ab, ac', 'c' => 'ad, ae'],
                'operator'   =>  'in',
                'test'       => ['aa', 'ab'],
                'expected'   => ['a', 'b'],
                'split'      => ','
            ],

            // not in
            [
                'attributes' => ['a' => 'a', 'b' => 'b', 'c' => 'c'],
                'operator'   =>  'not in',
                'test'       => ['a', 'c'],
                'expected'   => ['b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'aa, ab', 'b' => 'ab, ac', 'c' => 'ad, ae'],
                'operator'   =>  'not in',
                'test'       => ['aa', 'ab'],
                'expected'   => ['c'],
                'split'      => ','
            ],

            // contains
            [
                'attributes' => ['a' => 'abc', 'b' => 'def'],
                'operator'   =>  '*=',
                'test'       => 'b',
                'expected'   => ['a'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'aa, ab', 'b' => 'ba, bb', 'c' => 'ca'],
                'operator'   =>  '*=',
                'test'       => 'b',
                'expected'   => ['a', 'b'],
                'split'      => ','
            ],

            // not contains
            [
                'attributes' => ['a' => 'abc', 'b' => 'def'],
                'operator'   =>  '!*=',
                'test'       => 'b',
                'expected'   => ['b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'aa, ab', 'b' => 'ba, bb', 'c' => 'ca'],
                'operator'   =>  '!*=',
                'test'       => 'b',
                'expected'   => ['c'],
                'split'      => ','
            ],

            // more
            [
                'attributes' => ['a' => 1, 'b' => 2],
                'operator'   =>  '>',
                'test'       => 1,
                'expected'   => ['b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
                'operator'   =>  '>',
                'test'       => 2,
                'expected'   => ['b', 'c'],
                'split'      => ','
            ],

            // min
            [
                'attributes' => ['a' => 1, 'b' => 2, 'c' => 3],
                'operator'   =>  '>=',
                'test'       => 2,
                'expected'   => ['b', 'c'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
                'operator'   =>  '>=',
                'test'       => 3,
                'expected'   => ['b', 'c'],
                'split'      => ','
            ],

            // less
            [
                'attributes' => ['a' => 1, 'b' => 2],
                'operator'   =>  '<',
                'test'       => 2,
                'expected'   => ['a'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
                'operator'   =>  '<',
                'test'       => 5,
                'expected'   => ['a', 'b'],
                'split'      => ','
            ],

            // max
            [
                'attributes' => ['a' => 1, 'b' => 2, 'c' => 3],
                'operator'   =>  '<=',
                'test'       => 2,
                'expected'   => ['a', 'b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
                'operator'   =>  '<=',
                'test'       => 4,
                'expected'   => ['a', 'b'],
                'split'      => ','
            ],

            // starts with
            [
                'attributes' => ['a' => 'aa', 'b' => 'bb'],
                'operator'   =>  '^=',
                'test'       => 'a',
                'expected'   => ['a'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'a foo, a bar', 'b' => 'b foo, c bar', 'c' => 'c foo, c bar'],
                'operator'   =>  '^=',
                'test'       => 'c',
                'expected'   => ['b', 'c'],
                'split'      => ','
            ],

            // not starts with
            [
                'attributes' => ['a' => 'aa', 'b' => 'bb'],
                'operator'   =>  '!^=',
                'test'       => 'a',
                'expected'   => ['b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'a foo, a bar', 'b' => 'b foo, c bar', 'c' => 'c foo, c bar'],
                'operator'   =>  '!^=',
                'test'       => 'c',
                'expected'   => ['a'],
                'split'      => ','
            ],

            // ends with
            [
                'attributes' => ['a' => 'aa', 'b' => 'bb'],
                'operator'   =>  '$=',
                'test'       => 'a',
                'expected'   => ['a'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'foo a, bar a', 'b' => 'foo b, bar c', 'c' => 'foo c, bar c'],
                'operator'   =>  '$=',
                'test'       => 'c',
                'expected'   => ['b', 'c'],
                'split'      => ','
            ],

            // not ends with
            [
                'attributes' => ['a' => 'aa', 'b' => 'bb'],
                'operator'   =>  '!$=',
                'test'       => 'a',
                'expected'   => ['b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'foo a, bar a', 'b' => 'foo b, bar c', 'c' => 'foo c, bar c'],
                'operator'   =>  '!$=',
                'test'       => 'c',
                'expected'   => ['a'],
                'split'      => ','
            ],

            // between
            [
                'attributes' => ['a' => 1, 'b' => 2, 'c' => 3],
                'operator'   =>  'between',
                'test'       => [2, 3],
                'expected'   => ['b', 'c'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
                'operator'   =>  'between',
                'test'       => [1, 4],
                'expected'   => ['a', 'b'],
                'split'      => ','
            ],

            // match
            [
                'attributes' => ['a' => 'abc', 'b' => 'ABC'],
                'operator'   =>  '*',
                'test'       => '/[a-z]+/',
                'expected'   => ['a'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'abc, def', 'b' => 'ABC, DEF', 'c' => 'abc, DEF'],
                'operator'   =>  '*',
                'test'       => '/[a-z]+/',
                'expected'   => ['a', 'c'],
                'split'      => ','
            ],

            // not match
            [
                'attributes' => ['a' => 'abc', 'b' => 'ABC'],
                'operator'   =>  '!*',
                'test'       => '/[a-z]+/',
                'expected'   => ['b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'abc, def', 'b' => 'ABC, DEF', 'c' => 'abc, DEF'],
                'operator'   =>  '!*',
                'test'       => '/[a-z]+/',
                'expected'   => ['b'],
                'split'      => ','
            ],

            // minlength
            [
                'attributes' => ['a' => 'abc', 'b' => 'defg'],
                'operator'   =>  'minlength',
                'test'       => 4,
                'expected'   => ['b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'abc', 'b' => 'defg', 'c' => 'hijklm'],
                'operator'   =>  'minlength',
                'test'       => 4,
                'expected'   => ['b', 'c'],
                'split'      => ','
            ],

            // maxlength
            [
                'attributes' => ['a' => 'abc', 'b' => 'defg'],
                'operator'   =>  'maxlength',
                'test'       => 3,
                'expected'   => ['a'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'abc', 'b' => 'defg', 'c' => 'hijklm'],
                'operator'   =>  'maxlength',
                'test'       => 3,
                'expected'   => ['a'],
                'split'      => ','
            ],

            // minwords
            [
                'attributes' => ['a' => 'hello world', 'b' => 'hello'],
                'operator'   =>  'minwords',
                'test'       => 2,
                'expected'   => ['a'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'hello world, so great', 'b' => 'hello, great'],
                'operator'   =>  'minwords',
                'test'       => 2,
                'expected'   => ['a'],
                'split'      => ','
            ],

            // maxwords
            [
                'attributes' => ['a' => 'hello world', 'b' => 'hello'],
                'operator'   =>  'maxwords',
                'test'       => 1,
                'expected'   => ['b'],
                'split'      => false
            ],
            [
                'attributes' => ['a' => 'hello world, great', 'b' => 'hello, great'],
                'operator'   =>  'maxwords',
                'test'       => 1,
                'expected'   => ['b'],
                'split'      => ','
            ],

        ];
    }

    /**
     * @dataProvider filterDataProvider
     */
    public function testFilterBy($attributes, $operator, $test, $expected, $split)
    {
        $data = [];

        foreach ($attributes as $attributeKey => $attributeValue) {
            $data[$attributeKey] = [
                'attribute' => $attributeValue
            ];
        }

        $collection = new Collection($data);
        $result     = $collection->filterBy('attribute', $operator, $test, $split);

        $this->assertEquals($expected, $result->keys(), $operator);
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
