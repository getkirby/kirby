<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\A
 */
class ATest extends TestCase
{
    protected function _array()
    {
        return [
            'cat'  => 'miao',
            'dog'  => 'wuff',
            'bird' => 'tweet'
        ];
    }

    /**
     * @covers ::apply
     */
    public function testApply()
    {
        $array = [
            'level' => [
                'foo' => 'bar',
                'homer' => function () {
                    return 'simpson';
                }
            ],
            'a' => function ($b) {
                return $b;
            }
        ];

        $expected = [
            'level' => [
                'foo' => 'bar',
                'homer' => 'simpson'
            ],
            'a' => 'b'
        ];

        $this->assertSame($expected, A::apply($array, 'b'));
        $this->assertSame($expected, A::apply($array, 'b', 'c'));

        $array['a'] = function ($b, $c) {
            return $b . ' or ' . $c;
        };
        $expected['a'] = 'b or c';
        $this->assertSame($expected, A::apply($array, 'b', 'c'));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $array = $this->_array();

        // non-array
        $this->assertSame('test', A::get('test', 'test'));

        // single key
        $this->assertSame('miao', A::get($array, 'cat'));

        // multiple keys
        $this->assertSame([
            'cat'  => 'miao',
            'dog'  => 'wuff',
        ], A::get($array, ['cat', 'dog']));

        // null key
        $this->assertSame($array, A::get($array, null));

        // fallback value
        $this->assertSame(null, A::get($array, 'elephant'));
        $this->assertSame('toot', A::get($array, 'elephant', 'toot'));

        $this->assertSame([
            'cat' => 'miao',
            'elephant'  => null,
        ], A::get($array, ['cat', 'elephant']));

        $this->assertSame([
            'cat' => 'miao',
            'elephant'  => 'toot',
        ], A::get($array, ['cat', 'elephant'], 'toot'));
    }

    /**
     * @covers ::get
     */
    public function testGetWithDotNotation()
    {
        $data = [
            'grand.ma' => $grandma = [
                'mother' => $mother = [
                    'child' => $child = 'a',
                    'another.nested.child' => $anotherChild = 'b',
                ],
                'uncle.dot' => $uncle = 'uncle',
                'cousins' => [
                    ['name' => $cousinA = 'tick'],
                    ['name' => $cousinB = 'trick'],
                    ['name' => $cousinC = 'track'],
                ]
            ],
            'grand.ma.mother' => $anotherMother = 'another mother'
        ];

        $this->assertSame($grandma, A::get($data, 'grand.ma'));
        $this->assertSame($uncle, A::get($data, 'grand.ma.uncle.dot'));
        $this->assertSame($anotherMother, A::get($data, 'grand.ma.mother'));
        $this->assertSame($child, A::get($data, 'grand.ma.mother.child'));
        $this->assertSame($anotherChild, A::get($data, 'grand.ma.mother.another.nested.child'));
        $this->assertSame($cousinC, A::get($data, 'grand.ma.cousins.2.name'));

        // with default
        $this->assertSame('default', A::get($data, 'grand', 'default'));
        $this->assertSame('default', A::get($data, 'grand.grandaunt', 'default'));
        $this->assertSame('default', A::get($data, 'grand.ma.aunt', 'default'));
        $this->assertSame('default', A::get($data, 'grand.ma.uncle.dot.cousin', 'default'));
        $this->assertSame('default', A::get($data, 'grand.ma.mother.sister', 'default'));
        $this->assertSame('default', A::get($data, 'grand.ma.mother.child.grandchild', 'default'));
        $this->assertSame('default', A::get($data, 'grand.ma.mother.child.another.nested.sister', 'default'));
        $this->assertSame('default', A::get($data, 'grand.ma.cousins.4.name', 'default'));
    }

    /**
     * @covers ::get
     */
    public function testGetWithNonexistingOptions()
    {
        $data = [
            // 'alexander.the.great' => 'should not be fetched',
            'alexander' => 'not great yet'
        ];

        $this->assertNull(A::get($data, 'alexander.the.greate'));
        $this->assertSame('not great yet', A::get($data, 'alexander'));
    }

    /**
     * @covers ::map
     */
    public function testMap()
    {
        $array = [
            'Peter', 'Bob', 'Mary'
        ];

        $expected = [
            ['name' => 'Peter'],
            ['name' => 'Bob'],
            ['name' => 'Mary']
        ];

        $this->assertSame($expected, A::map($array, function ($name) {
            return ['name' => $name];
        }));
    }

    public function testMapWithFunction()
    {
        $array    = [' A ', 'B ', ' C'];
        $expected = ['A', 'B', 'C'];

        $this->assertSame($expected, A::map($array, 'trim'));
    }

    public function testMapWithClassMethod()
    {
        $array    = ['a', 'b', 'c'];
        $expected = ['A', 'B', 'C'];

        $this->assertSame($expected, A::map($array, 'Str::upper'));
    }

    /**
     * @covers ::merge
     */
    public function testMerge()
    {

        // simple non-associative arrays
        $this->assertSame(['a', 'b', 'c', 'd'], A::merge(['a', 'b'], ['c', 'd']));
        $this->assertSame(['a', 'b', 'c', 'd', 'a'], A::merge(['a', 'b'], ['c', 'd', 'a']));

        // simple associative arrays
        $this->assertSame(['a' => 'b', 'c' => 'd'], A::merge(['a' => 'b'], ['c' => 'd']));
        $this->assertSame(['a' => 'c'], A::merge(['a' => 'b'], ['a' => 'c']));

        // recursive merging
        $this->assertSame(['a' => ['b', 'c', 'b', 'd']], A::merge(['a' => ['b', 'c']], ['a' => ['b', 'd']]));
        $this->assertSame(['a' => ['b' => 'd', 'd' => 'e']], A::merge(['a' => ['b' => 'c', 'd' => 'e']], ['a' => ['b' => 'd']]));
        $this->assertSame(['a' => ['b', 'c']], A::merge(['a' => 'b'], ['a' => ['b', 'c']]));
        $this->assertSame(['a' => 'b'], A::merge(['a' => ['b', 'c']], ['a' => 'b']));

        // append feature
        $this->assertSame(['a', 'b', 'c', 'd', 'a'], A::merge([1 => 'a', 4 => 'b'], [1 => 'c', 3 => 'd', 5 => 'a']));
        $this->assertSame(['a', 'b', 'c', 'd', 'a'], A::merge([1 => 'a', 4 => 'b'], [1 => 'c', 3 => 'd', 5 => 'a'], true));
        $this->assertSame([1 => 'c', 4 => 'b', 3 => 'd', 5 => 'a'], A::merge([1 => 'a', 4 => 'b'], [1 => 'c', 3 => 'd', 5 => 'a'], false));
        $this->assertSame(['a' => ['b', 'c', 'e', 'd']], A::merge(['a' => [1 => 'b', 4 => 'c']], ['a' => [1 => 'e', 3 => 'd']], true));
        $this->assertSame(['a' => [1 => 'c', 4 => 'b', 3 => 'd', 5 => 'a']], A::merge(['a' => [1 => 'a', 4 => 'b']], ['a' => [1 => 'c', 3 => 'd', 5 => 'a']], false));

        // replace feature
        $a = [
            'a' => ['a', 'b', 'c']
        ];

        $b = [
            'a' => ['d', 'e', 'f']
        ];

        $this->assertSame($b, A::merge($a, $b, A::MERGE_REPLACE));
    }

    /**
     * @covers ::pluck
     */
    public function testPluck()
    {
        $array = [
            [ 'id' => 1, 'username' => 'bastian'],
            [ 'id' => 2, 'username' => 'sonja'],
            [ 'id' => 3, 'username' => 'lukas']
        ];

        $this->assertSame([
            'bastian',
            'sonja',
            'lukas'
        ], A::pluck($array, 'username'));
    }

    /**
     * @covers ::shuffle
     */
    public function testShuffle()
    {
        $array = $this->_array();
        $shuffled = A::shuffle($array);

        $this->assertSame($array['cat'], $shuffled['cat']);
        $this->assertSame($array['dog'], $shuffled['dog']);
        $this->assertSame($array['bird'], $shuffled['bird']);
    }

    /**
     * @covers ::first
     */
    public function testFirst()
    {
        $this->assertSame('miao', A::first($this->_array()));
    }

    /**
     * @covers ::last
     */
    public function testLast()
    {
        $this->assertSame('tweet', A::last($this->_array()));
    }

    /**
     * @covers ::fill
     */
    public function testFill()
    {
        $array = [
            'miao',
            'wuff',
            'tweet'
        ];

        // placholder
        $this->assertSame([
            'miao',
            'wuff',
            'tweet',
            'placeholder'
        ], A::fill($array, 4));

        // custom value
        $this->assertSame([
            'miao',
            'wuff',
            'tweet',
            'elephant',
            'elephant'
        ], A::fill($array, 5, 'elephant'));
    }

    /**
     * @covers ::move
     */
    public function testMove()
    {
        $input = [
            'a',
            'b',
            'c',
            'd'
        ];

        $this->assertSame(['a', 'b', 'c', 'd'], A::move($input, 0, 0));
        $this->assertSame(['b', 'a', 'c', 'd'], A::move($input, 0, 1));
        $this->assertSame(['b', 'c', 'a', 'd'], A::move($input, 0, 2));
        $this->assertSame(['b', 'c', 'd', 'a'], A::move($input, 0, 3));

        $this->assertSame(['d', 'a', 'b', 'c'], A::move($input, 3, 0));
        $this->assertSame(['c', 'a', 'b', 'd'], A::move($input, 2, 0));
        $this->assertSame(['b', 'a', 'c', 'd'], A::move($input, 1, 0));
    }

    /**
     * @covers ::move
     */
    public function testMoveWithInvalidFrom()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid "from" index');

        A::move(['a', 'b', 'c'], -1, 2);
    }

    /**
     * @covers ::move
     */
    public function testMoveWithInvalidTo()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid "to" index');

        A::move(['a', 'b', 'c'], 0, 4);
    }

    /**
     * @covers ::missing
     */
    public function testMissing()
    {
        $required = ['cat', 'elephant'];

        $this->assertSame(['elephant'], A::missing($this->_array(), $required));
        $this->assertSame([], A::missing($this->_array(), ['cat']));
    }

    /**
     * @covers ::nest
     */
    public function testNest()
    {
        // simple example
        $input = [
            'a' => 'a value',
            'b.c' => [
                'd.e.f' => 'another value'
            ]
        ];
        $expected = [
            'a' => 'a value',
            'b' => [
                'c' => [
                    'd' => [
                        'e' => [
                            'f' => 'another value'
                        ]
                    ]
                ]
            ]
        ];
        $this->assertSame($expected, A::nest($input));

        // ignored key
        $input = [
            'a' => 'a value',
            'b' => 'another value',
            'b.c' => [
                'd.e.f' => 'a third value'
            ]
        ];
        $expected = $input;
        $this->assertSame($expected, A::nest($input, ['b']));

        // nested ignored key
        $expected = [
            'a' => 'a value',
            'b' => [
                'c' => [
                    'd.e.f' => 'a third value'
                ]
            ]
        ];
        $this->assertSame($expected, A::nest($input, ['b.c']));

        // ignored key with partially nested input
        $input = $expected;
        $this->assertSame($expected, A::nest($input, ['b.c']));

        // recursive array replacement
        $input = [
            // replace strings with arrays within deep structures
            'a' => 'this will be overwritten',
            'a.b' => [
                'c' => 'this as well',
                'd' => 'and this',
                'e' => 'but this will be preserved'
            ],
            'a.b.c' => 'a value',
            'a.b.d.f' => 'another value',

            // replace arrays with strings
            'g.h' => [
                'i' => 'this will be overwritten as well'
            ],
            'g' => 'and another value',

            // replacements within two different trees
            'j.k' => [
                'l' => 'this will be replaced',
                'm' => 'but this will not be'
            ],
            'j' => [
                'k.l' => 'a nice replacement',
                'n' => 'and this string is nice too'
            ]
        ];
        $expected = [
            'a' => [
                'b' => [
                    'c' => 'a value',
                    'd' => [
                        'f' => 'another value'
                    ],
                    'e' => 'but this will be preserved'
                ]
            ],
            'g' => 'and another value',
            'j' => [
                'k' => [
                    'l' => 'a nice replacement',
                    'm' => 'but this will not be'
                ],
                'n' => 'and this string is nice too'
            ]
        ];
        $this->assertSame($expected, A::nest($input));

        // merged arrays
        $input1 = [
            'a' => 'a-1',
            'b' => [
                'c' => 'b.c-1',
                'd' => 'b.d-1'
            ],
            'e.f' => [
                'g.h' => 'e.f.g.h-1',
                'g.i' => 'e.f.g.i-1'
            ],
            'l' => [
                'm' => 'l.m-1',
                'o.p' => 'l.o.p-1'
            ]
        ];
        $input2 = [
            'a' => 'a-2',
            'b.c' => 'b.c-2',
            'e' => [
                'f.g' => [
                    'h' => 'e.f.g.h-2',
                    'j' => 'e.f.g.j-2'
                ],
                'k' => 'e.k-2'
            ],
            'l' => [
                'm.n' => 'l.m.n-2',
                'o' => 'l.o-2'
            ]
        ];
        $expected = [
            'a' => 'a-2',
            'b' => [
                'c' => 'b.c-2',
                'd' => 'b.d-1'
            ],
            'e' => [
                'f' => [
                    'g' => [
                        'h' => 'e.f.g.h-2',
                        'i' => 'e.f.g.i-1',
                        'j' => 'e.f.g.j-2'
                    ]
                ],
                'k' => 'e.k-2'
            ],
            'l' => [
                'm' => 'l.m-1',
                'o.p' => 'l.o.p-1',
                'm.n' => 'l.m.n-2',
                'o' => 'l.o-2'
            ]
        ];
        $this->assertSame($expected, A::nest(array_replace_recursive($input1, $input2), ['l.m', 'l.o']));
        $this->assertSame($expected, A::nest(A::merge($input1, $input2, A::MERGE_REPLACE), ['l.m', 'l.o']));

        // with numeric keys
        $input = [
            'a' => 'a value',
            'b.2.e.f' => 'another value'
        ];
        $expected = [
            'a' => 'a value',
            'b' => [
                2 => [
                    'e' => [
                        'f' => 'another value'
                    ]
                ]
            ]
        ];
        $this->assertSame($expected, A::nest($input));
    }

    /**
     * @covers ::nestByKeys
     */
    public function testNestByKeys()
    {
        $this->assertSame('test', A::nestByKeys('test', []));
        $this->assertSame(['a' => 'test'], A::nestByKeys('test', ['a']));
        $this->assertSame(['a' => ['b' => 'test']], A::nestByKeys('test', ['a', 'b']));
    }

    /**
     * @covers ::sort
     */
    public function testSort()
    {
        $array = [
            [ 'id' => 1, 'username' => 'bastian'],
            [ 'id' => 2, 'username' => 'sonja'],
            [ 'id' => 3, 'username' => 'lukas']
        ];

        // ASC
        $sorted = A::sort($array, 'username', 'asc');

        $this->assertSame(0, array_search('bastian', array_column($sorted, 'username')));
        $this->assertSame(2, array_search('sonja', array_column($sorted, 'username')));
        $this->assertSame(1, array_search('lukas', array_column($sorted, 'username')));

        // DESC
        $sorted = A::sort($array, 'username', 'desc');

        $this->assertSame(2, array_search('bastian', array_column($sorted, 'username')));
        $this->assertSame(0, array_search('sonja', array_column($sorted, 'username')));
        $this->assertSame(1, array_search('lukas', array_column($sorted, 'username')));

        //SORT_NATURAL
        $array = [
            ['file' => 'img12.png'],
            ['file' => 'img10.png'],
            ['file' => 'img2.png'],
            ['file' => 'img1.png']
        ];

        $regular = A::sort($array, 'file', 'asc');
        $natural = A::sort($array, 'file', 'asc', SORT_NATURAL);

        $this->assertSame(0, array_search('img1.png', array_column($regular, 'file')));
        $this->assertSame(1, array_search('img10.png', array_column($regular, 'file')));
        $this->assertSame(2, array_search('img12.png', array_column($regular, 'file')));
        $this->assertSame(3, array_search('img2.png', array_column($regular, 'file')));

        $this->assertSame(0, array_search('img1.png', array_column($natural, 'file')));
        $this->assertSame(1, array_search('img2.png', array_column($natural, 'file')));
        $this->assertSame(2, array_search('img10.png', array_column($natural, 'file')));
        $this->assertSame(3, array_search('img12.png', array_column($natural, 'file')));
    }

    /**
     * @covers ::isAssociative
     */
    public function testIsAssociative()
    {
        $yes = $this->_array();
        $no = ['cat', 'dog', 'bird'];

        $this->assertTrue(A::isAssociative($yes));
        $this->assertFalse(A::isAssociative($no));
    }

    /**
     * @covers ::average
     */
    public function testAverage()
    {
        $array = [5, 2, 4, 7, 9.7];

        $this->assertSame(6.0, A::average($array));
        $this->assertSame(5.5, A::average($array, 1));
        $this->assertSame(5.54, A::average($array, 2));
    }

    /**
     * @covers ::extend
     */
    public function testExtend()
    {
        // simple
        $a = $this->_array();
        $b = [
            'elephant' => 'toot',
            'snake'    => 'zzz',
            'fox'      => 'what does the fox say?'
        ];

        $merged = [
            'cat'      => 'miao',
            'dog'      => 'wuff',
            'bird'     => 'tweet',
            'elephant' => 'toot',
            'snake'    => 'zzz',
            'fox'      => 'what does the fox say?'
        ];

        $this->assertSame($merged, A::extend($a, $b));

        // complex
        $a = [
            'verb'         => 'care',
            'prepositions' => ['not for', 'about', 'of']
        ];
        $b = [
            'prepositions' => ['for'],
            'object'       => 'others'
        ];

        $merged = [
            'verb'         => 'care',
            'prepositions' => ['not for', 'about', 'of', 'for'],
            'object'       => 'others'
        ];

        $this->assertSame($merged, A::extend($a, $b));
    }

    /**
     * @covers ::join
     */
    public function testJoin()
    {
        $array = ['a', 'b', 'c'];
        $this->assertSame('a, b, c', A::join($array));

        $array = ['a', 'b', 'c'];
        $this->assertSame('a/b/c', A::join($array, '/'));

        $this->assertSame('a/b/c', A::join('a/b/c'));
    }

    /**
     * @covers ::update
     */
    public function testUpdate()
    {
        $array = $this->_array();
        $updated = [
            'cat'  => 'meow',
            'dog'  => 'wuff',
            'bird' => 'tweet'
        ];

        // value
        $this->assertSame($updated, A::update($array, ['cat' => 'meow']));

        // callback
        $this->assertSame($updated, A::update($array, ['cat' => function ($value) {
            return 'meow';
        }]));
    }

    /**
     * @covers ::wrap
     */
    public function testWrap()
    {
        $result = A::wrap($expected = ['a', 'b']);
        $this->assertSame($expected, $result);

        $result = A::wrap('a');
        $this->assertSame(['a'], $result);

        $result = A::wrap(null);
        $this->assertSame([], $result);
    }
}
