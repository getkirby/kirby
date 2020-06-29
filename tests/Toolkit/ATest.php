<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

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

    public function testGet()
    {
        $array = $this->_array();

        // non-array
        $this->assertEquals('test', A::get('test', 'test'));

        // single key
        $this->assertEquals('miao', A::get($array, 'cat'));

        // multiple keys
        $this->assertEquals([
            'cat'  => 'miao',
            'dog'  => 'wuff',
        ], A::get($array, ['cat', 'dog']));

        // null key
        $this->assertEquals($array, A::get($array, null));

        // fallback value
        $this->assertEquals(null, A::get($array, 'elephant'));
        $this->assertEquals('toot', A::get($array, 'elephant', 'toot'));

        $this->assertEquals([
            'cat' => 'miao',
            'elephant'  => null,
        ], A::get($array, ['cat', 'elephant']));

        $this->assertEquals([
            'cat' => 'miao',
            'elephant'  => 'toot',
        ], A::get($array, ['cat', 'elephant'], 'toot'));
    }

    public function testGetWithDotNotation()
    {
        $data = [
            'grand.ma' => $grandma = [
                'mother' => $mother = [
                    'child' => $child = 'a',
                    'another.nested.child' => $anotherChild = 'b',
                ],
                'uncle.dot' => $uncle = 'uncle'
            ],
            'grand.ma.mother' => $anotherMother = 'another mother'
        ];

        $this->assertEquals($grandma, A::get($data, 'grand.ma'));
        $this->assertEquals($uncle, A::get($data, 'grand.ma.uncle.dot'));
        $this->assertEquals($anotherMother, A::get($data, 'grand.ma.mother'));
        $this->assertEquals($child, A::get($data, 'grand.ma.mother.child'));
        $this->assertEquals($anotherChild, A::get($data, 'grand.ma.mother.another.nested.child'));

        // with default
        $this->assertEquals('default', A::get($data, 'grand', 'default'));
        $this->assertEquals('default', A::get($data, 'grand.grandaunt', 'default'));
        $this->assertEquals('default', A::get($data, 'grand.ma.aunt', 'default'));
        $this->assertEquals('default', A::get($data, 'grand.ma.uncle.dot.cousin', 'default'));
        $this->assertEquals('default', A::get($data, 'grand.ma.mother.sister', 'default'));
        $this->assertEquals('default', A::get($data, 'grand.ma.mother.child.grandchild', 'default'));
        $this->assertEquals('default', A::get($data, 'grand.ma.mother.child.another.nested.sister', 'default'));
    }

    public function testGetWithNonexistingOptions()
    {
        $data = [
            // 'alexander.the.great' => 'should not be fetched',
            'alexander' => 'not great yet'
        ];

        $this->assertNull(A::get($data, 'alexander.the.greate'));
        $this->assertEquals('not great yet', A::get($data, 'alexander'));
    }

    public function testMerge()
    {

        // simple non-associative arrays
        $this->assertEquals(['a', 'b', 'c', 'd'], A::merge(['a', 'b'], ['c', 'd']));
        $this->assertEquals(['a', 'b', 'c', 'd', 'a'], A::merge(['a', 'b'], ['c', 'd', 'a']));

        // simple associative arrays
        $this->assertEquals(['a' => 'b', 'c' => 'd'], A::merge(['a' => 'b'], ['c' => 'd']));
        $this->assertEquals(['a' => 'c'], A::merge(['a' => 'b'], ['a' => 'c']));

        // recursive merging
        $this->assertEquals(['a' => ['b', 'c', 'b', 'd']], A::merge(['a' => ['b', 'c']], ['a' => ['b', 'd']]));
        $this->assertEquals(['a' => ['b' => 'd', 'd' => 'e']], A::merge(['a' => ['b' => 'c', 'd' => 'e']], ['a' => ['b' => 'd']]));
        $this->assertEquals(['a' => ['b', 'c']], A::merge(['a' => 'b'], ['a' => ['b', 'c']]));
        $this->assertEquals(['a' => 'b'], A::merge(['a' => ['b', 'c']], ['a' => 'b']));

        // append feature
        $this->assertEquals(['a', 'b', 'c', 'd', 'a'], A::merge([1 => 'a', 4 => 'b'], [1 => 'c', 3 => 'd', 5 => 'a']));
        $this->assertEquals(['a', 'b', 'c', 'd', 'a'], A::merge([1 => 'a', 4 => 'b'], [1 => 'c', 3 => 'd', 5 => 'a'], true));
        $this->assertEquals([1 => 'c', 3 => 'd', 4 => 'b', 5 => 'a'], A::merge([1 => 'a', 4 => 'b'], [1 => 'c', 3 => 'd', 5 => 'a'], false));
        $this->assertEquals(['a' => ['b', 'c', 'e', 'd']], A::merge(['a' => [1 => 'b', 4 => 'c']], ['a' => [1 => 'e', 3 => 'd']], true));
        $this->assertEquals(['a' => [1 => 'c', 3 => 'd', 4 => 'b', 5 => 'a']], A::merge(['a' => [1 => 'a', 4 => 'b']], ['a' => [1 => 'c', 3 => 'd', 5 => 'a']], false));

        // replace feature
        $a = [
            'a' => ['a', 'b', 'c']
        ];

        $b = [
            'a' => ['d', 'e', 'f']
        ];

        $this->assertEquals($b, A::merge($a, $b, A::MERGE_REPLACE));
    }

    public function testPluck()
    {
        $array = [
            [ 'id' => 1, 'username' => 'bastian'],
            [ 'id' => 2, 'username' => 'sonja'],
            [ 'id' => 3, 'username' => 'lukas']
        ];

        $this->assertEquals([
            'bastian',
            'sonja',
            'lukas'
        ], A::pluck($array, 'username'));
    }

    public function testShuffle()
    {
        $array = $this->_array();
        $shuffled = A::shuffle($array);

        $this->assertEquals($array['cat'], $shuffled['cat']);
        $this->assertEquals($array['dog'], $shuffled['dog']);
        $this->assertEquals($array['bird'], $shuffled['bird']);
    }

    public function testFirst()
    {
        $this->assertEquals('miao', A::first($this->_array()));
    }

    public function testLast()
    {
        $this->assertEquals('tweet', A::last($this->_array()));
    }

    public function testFill()
    {
        $array = [
            'miao',
            'wuff',
            'tweet'
        ];

        // placholder
        $this->assertEquals([
            'miao',
            'wuff',
            'tweet',
            'placeholder'
        ], A::fill($array, 4));

        // custom value
        $this->assertEquals([
            'miao',
            'wuff',
            'tweet',
            'elephant',
            'elephant'
        ], A::fill($array, 5, 'elephant'));
    }

    public function testMove()
    {
        $input = [
            'a',
            'b',
            'c',
            'd'
        ];

        $this->assertEquals(['a', 'b', 'c', 'd'], A::move($input, 0, 0));
        $this->assertEquals(['b', 'a', 'c', 'd'], A::move($input, 0, 1));
        $this->assertEquals(['b', 'c', 'a', 'd'], A::move($input, 0, 2));
        $this->assertEquals(['b', 'c', 'd', 'a'], A::move($input, 0, 3));

        $this->assertEquals(['d', 'a', 'b', 'c'], A::move($input, 3, 0));
        $this->assertEquals(['c', 'a', 'b', 'd'], A::move($input, 2, 0));
        $this->assertEquals(['b', 'a', 'c', 'd'], A::move($input, 1, 0));
    }

    public function testMoveWithInvalidFrom()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid "from" index');

        A::move(['a', 'b', 'c'], -1, 2);
    }

    public function testMoveWithInvalidTo()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid "to" index');

        A::move(['a', 'b', 'c'], 0, 4);
    }

    public function testMissing()
    {
        $required = ['cat', 'elephant'];

        $this->assertEquals(['elephant'], A::missing($this->_array(), $required));
        $this->assertEquals([], A::missing($this->_array(), ['cat']));
    }

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
    }

    public function testNestByKeys()
    {
        $this->assertSame('test', A::nestByKeys('test', []));
        $this->assertSame(['a' => 'test'], A::nestByKeys('test', ['a']));
        $this->assertSame(['a' => ['b' => 'test']], A::nestByKeys('test', ['a', 'b']));
    }

    public function testSort()
    {
        $array = [
            [ 'id' => 1, 'username' => 'bastian'],
            [ 'id' => 2, 'username' => 'sonja'],
            [ 'id' => 3, 'username' => 'lukas']
        ];

        // ASC
        $sorted = A::sort($array, 'username', 'asc');

        $this->assertEquals(0, array_search('bastian', array_column($sorted, 'username')));
        $this->assertEquals(2, array_search('sonja', array_column($sorted, 'username')));
        $this->assertEquals(1, array_search('lukas', array_column($sorted, 'username')));

        // DESC
        $sorted = A::sort($array, 'username', 'desc');

        $this->assertEquals(2, array_search('bastian', array_column($sorted, 'username')));
        $this->assertEquals(0, array_search('sonja', array_column($sorted, 'username')));
        $this->assertEquals(1, array_search('lukas', array_column($sorted, 'username')));

        //SORT_NATURAL
        $array = [
            ['file' => 'img12.png'],
            ['file' => 'img10.png'],
            ['file' => 'img2.png'],
            ['file' => 'img1.png']
        ];

        $regular = A::sort($array, 'file', 'asc');
        $natural = A::sort($array, 'file', 'asc', SORT_NATURAL);

        $this->assertEquals(0, array_search('img1.png', array_column($regular, 'file')));
        $this->assertEquals(1, array_search('img10.png', array_column($regular, 'file')));
        $this->assertEquals(2, array_search('img12.png', array_column($regular, 'file')));
        $this->assertEquals(3, array_search('img2.png', array_column($regular, 'file')));

        $this->assertEquals(0, array_search('img1.png', array_column($natural, 'file')));
        $this->assertEquals(1, array_search('img2.png', array_column($natural, 'file')));
        $this->assertEquals(2, array_search('img10.png', array_column($natural, 'file')));
        $this->assertEquals(3, array_search('img12.png', array_column($natural, 'file')));
    }

    public function testIsAssociative()
    {
        $yes = $this->_array();
        $no = ['cat', 'dog', 'bird'];

        $this->assertTrue(A::isAssociative($yes));
        $this->assertFalse(A::isAssociative($no));
    }

    public function testAverage()
    {
        $array = [5, 2, 4, 7, 9.7];

        $this->assertEquals(6, A::average($array));
        $this->assertEquals(5.5, A::average($array, 1));
        $this->assertEquals(5.54, A::average($array, 2));
    }

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

        $this->assertEquals($merged, A::extend($a, $b));

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

        $this->assertEquals($merged, A::extend($a, $b));
    }

    public function testJoin()
    {
        $array = ['a', 'b', 'c'];
        $this->assertEquals('a, b, c', A::join($array));

        $array = ['a', 'b', 'c'];
        $this->assertEquals('a/b/c', A::join($array, '/'));

        $this->assertEquals('a/b/c', A::join('a/b/c'));
    }

    public function testUpdate()
    {
        $array = $this->_array();
        $updated = [
            'cat'  => 'meow',
            'dog'  => 'wuff',
            'bird' => 'tweet'
        ];

        // value
        $this->assertEquals($updated, A::update($array, ['cat' => 'meow']));

        // callback
        $this->assertEquals($updated, A::update($array, ['cat' => function ($value) {
            return 'meow';
        }]));
    }

    public function testWrap()
    {
        $result = A::wrap($expected = ['a', 'b']);
        $this->assertEquals($expected, $result);

        $result = A::wrap('a');
        $this->assertEquals(['a'], $result);

        $result = A::wrap(null);
        $this->assertEquals([], $result);
    }
}
