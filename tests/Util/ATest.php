<?php

namespace Kirby\Util;

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

    public function testMissing()
    {
        $required = ['cat', 'elephant'];

        $this->assertEquals(['elephant'], A::missing($this->_array(), $required));
        $this->assertEquals([], A::missing($this->_array(), ['cat']));
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
}
