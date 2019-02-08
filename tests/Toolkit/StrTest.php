<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    public function testAfter()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertEquals(' Wörld', Str::after($string, 'ö'));
        $this->assertEquals(false, Str::after($string, 'Ö'));
        $this->assertEquals(false, Str::after($string, 'x'));

        // case insensitive
        $this->assertEquals(' Wörld', Str::after($string, 'ö', true));
        $this->assertEquals(' Wörld', Str::after($string, 'Ö', true));
        $this->assertEquals(false, Str::after($string, 'x'));

        // non existing chars
        $this->assertEquals(false, Str::after('string', '.'), 'string with non-existing character should return false');
    }

    public function testBefore()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertEquals('Hell', Str::before($string, 'ö'));
        $this->assertEquals(false, Str::before($string, 'Ö'));
        $this->assertEquals(false, Str::before($string, 'x'));

        // case insensitive
        $this->assertEquals('Hell', Str::before($string, 'ö', true));
        $this->assertEquals('Hell', Str::before($string, 'Ö', true));
        $this->assertEquals(false, Str::before($string, 'x'));
    }

    public function testBetween()
    {
        $this->assertEquals('trin', Str::between('string', 's', 'g'), 'string between s and g should be trin');
        $this->assertEquals(false, Str::between('string', 's', '.'), 'function with non-existing character should return false');
        $this->assertEquals(false, Str::between('string', '.', 'g'), 'function with non-existing character should return false');
    }

    public function testContains()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertTrue(Str::contains($string, 'Hellö'));
        $this->assertTrue(Str::contains($string, 'Wörld'));
        $this->assertFalse(Str::contains($string, 'hellö'));
        $this->assertFalse(Str::contains($string, 'wörld'));

        // case insensitive
        $this->assertTrue(Str::contains($string, 'Hellö', true));
        $this->assertTrue(Str::contains($string, 'Wörld', true));
        $this->assertTrue(Str::contains($string, 'hellö', true));
        $this->assertTrue(Str::contains($string, 'wörld', true));
    }

    public function testEncoding()
    {
        $this->assertEquals('UTF-8', Str::encoding('ÖÄÜ'));
    }

    public function testEndsWith()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertTrue(Str::endsWith($string, ''));
        $this->assertTrue(Str::endsWith($string, 'd'));
        $this->assertFalse(Str::endsWith($string, 'D'));
        $this->assertTrue(Str::endsWith($string, 'Wörld'));
        $this->assertFalse(Str::endsWith($string, 'WÖRLD'));

        // case insensitive
        $this->assertTrue(Str::endsWith($string, '', true));
        $this->assertTrue(Str::endsWith($string, 'd', true));
        $this->assertTrue(Str::endsWith($string, 'D', true));
        $this->assertTrue(Str::endsWith($string, 'Wörld', true));
        $this->assertTrue(Str::endsWith($string, 'WÖRLD', true));
    }

    public function testExcerpt()
    {
        $string   = 'This is a long text<br>with some html';
        $expected = 'This is a long text with …';
        $result   = Str::excerpt($string, 27);

        $this->assertEquals($expected, $result);
    }

    public function testExcerptWithoutChars()
    {
        $string   = 'This is a long text<br>with some html';
        $expected = 'This is a long text with some html';
        $result   = Str::excerpt($string);

        $this->assertEquals($expected, $result);
    }

    public function testExcerptWithoutStripping()
    {
        $string   = 'This is a long text<br>with some html';
        $expected = 'This is a long text<br>with …';
        $result   = Str::excerpt($string, 30, false);

        $this->assertEquals($expected, $result);
    }

    public function testExcerptWithDifferentRep()
    {
        $string   = 'This is a long text<br>with some html';
        $expected = 'This is a long text with ...';
        $result   = Str::excerpt($string, 27, true, '...');

        $this->assertEquals($expected, $result);
    }

    public function testExcerptWithSpaces()
    {
        $string   = 'This is a long text   <br>with some html';
        $expected = 'This is a long text with …';
        $result   = Str::excerpt($string, 27);

        $this->assertEquals($expected, $result);
    }

    public function testExcerptWithLineBreaks()
    {
        $string   = 'This is a long text ' . PHP_EOL . ' with some html';
        $expected = 'This is a long text with …';
        $result   = Str::excerpt($string, 27);

        $this->assertEquals($expected, $result);
    }

    public function testFloat()
    {
        $this->assertEquals('0', Str::float(false));
        $this->assertEquals('0', Str::float(null));
        $this->assertEquals('0', Str::float(0));
        $this->assertEquals('0', Str::float('0'));
        $this->assertEquals('1', Str::float(true));
        $this->assertEquals('1', Str::float(1));
        $this->assertEquals('1', Str::float('1'));
        $this->assertEquals('1.1', Str::float(1.1));
        $this->assertEquals('1.1', Str::float('1.1'));
        $this->assertEquals('1.1', Str::float('1,1'));
        $this->assertEquals('1.11', Str::float('1,11'));
        $this->assertEquals('1.111', Str::float('1,111'));
        $this->assertEquals('-1', Str::float(-1));
        $this->assertEquals('-1.1', Str::float(-1.1));
        $this->assertEquals('-1.11', Str::float('-1.11'));
        $this->assertEquals('-1.111', Str::float('-1,111'));
    }


    public function testFrom()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertEquals('ö Wörld', Str::from($string, 'ö'));
        $this->assertEquals(false, Str::from($string, 'Ö'));
        $this->assertEquals(false, Str::from($string, 'x'));

        // case insensitive
        $this->assertEquals('ö Wörld', Str::from($string, 'ö', true));
        $this->assertEquals('ö Wörld', Str::from($string, 'Ö', true));
        $this->assertEquals(false, Str::from($string, 'x'));
    }

    public function testLength()
    {
        $this->assertEquals(0, Str::length(''));
        $this->assertEquals(3, Str::length('abc'));
        $this->assertEquals(3, Str::length('öäü'));
        $this->assertEquals(6, Str::length('Aœ?_ßö'));
    }

    public function testLower()
    {
        $this->assertEquals('öäü', Str::lower('ÖÄÜ'));
        $this->assertEquals('öäü', Str::lower('Öäü'));
    }

    public function testLtrim()
    {
        $this->assertEquals('test', Str::ltrim(' test'));
        $this->assertEquals('test', Str::ltrim('  test'));
        $this->assertEquals('jpg', Str::ltrim('test.jpg', 'test.'));
    }

    public function testPosition()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertTrue(Str::position($string, 'H') === 0);
        $this->assertFalse(Str::position($string, 'h') === 0);
        $this->assertTrue(Str::position($string, 'ö') === 4);
        $this->assertFalse(Str::position($string, 'Ö') === 4);

        // case insensitive
        $this->assertTrue(Str::position($string, 'H', true) === 0);
        $this->assertTrue(Str::position($string, 'h', true) === 0);
        $this->assertTrue(Str::position($string, 'ö', true) === 4);
        $this->assertTrue(Str::position($string, 'Ö', true) === 4);
    }

    public function testRandom()
    {
        // choose a high length for a high probability of occurrence of a character of any type
        $length = 200;

        $this->assertRegexp("/^[[:alnum:]]+$/", Str::random());
        $this->assertInternalType('string', Str::random());
        $this->assertEquals($length, strlen(Str::random($length)));

        $this->assertRegexp("/^[[:alpha:]]+$/", Str::random($length, 'alpha'));

        $this->assertRegexp("/^[[:upper:]]+$/", Str::random($length, 'alphaUpper'));

        $this->assertRegexp("/^[[:lower:]]+$/", Str::random($length, 'alphaLower'));

        $this->assertRegexp("/^[[:digit:]]+$/", Str::random($length, 'num'));

        $this->assertFalse(Str::random($length, 'something invalid'));
    }

    public function testReplace()
    {
        // simple strings with limits
        $this->assertEquals('ths s a strng', Str::replace('this is a string', 'i', ''));
        $this->assertEquals('this is a string', Str::replace('this is a string', 'i', '', 0));
        $this->assertEquals('ths is a string', Str::replace('this is a string', 'i', '', 1));
        $this->assertEquals('ths s a string', Str::replace('this is a string', 'i', '', 2));
        $this->assertEquals('ths s a strng', Str::replace('this is a string', 'i', '', 3));
        $this->assertEquals('ths s a strng', Str::replace('this is a string', 'i', '', 1000));
        $this->assertEquals('th!s !s a string', Str::replace('this is a string', 'i', '!', 2));
        $this->assertEquals('th?!s ?!s a string', Str::replace('this is a string', 'i', '?!', 2));
        $this->assertEquals('that also is a string', Str::replace('this is a string', 'this', 'that also', 1));
        $this->assertEquals('this is aeä string', Str::replace('this is ää string', 'ä', 'ae', 1));
        $this->assertEquals('this is aeae string', Str::replace('this is ää string', 'ä', 'ae', 2));
        $this->assertEquals('this is äa string', Str::replace('this is aa string', 'a', 'ä', 1));
        $this->assertEquals('this is ää string', Str::replace('this is aa string', 'a', 'ä', 2));

        // $subject as array
        $this->assertEquals(['ths', 's', 'a', 'strng'], Str::replace(['this', 'is', 'a', 'string'], 'i', ''));
        $this->assertEquals(['this', 'is', 'a', 'string'], Str::replace(['this', 'is', 'a', 'string'], 'i', '', 0));
        $this->assertEquals(['ths', 's', 'a', 'strng'], Str::replace(['this', 'is', 'a', 'string'], 'i', '', 1));
        $this->assertEquals(['ths', 's', 'a', 'strng'], Str::replace(['this', 'is', 'a', 'striing'], 'i', ''));
        $this->assertEquals(['this', 'is', 'a', 'striing'], Str::replace(['this', 'is', 'a', 'striing'], 'i', '', 0));
        $this->assertEquals(['ths', 's', 'a', 'string'], Str::replace(['this', 'is', 'a', 'striing'], 'i', '', 1));
        $this->assertEquals(['ths', 's', 'a', 'strng'], Str::replace(['this', 'is', 'a', 'striing'], 'i', '', 2));

        // $subject as Collection
        $subjects = new Collection(['this', 'is', 'a', 'striing']);
        $this->assertEquals(['ths', 's', 'a', 'strng'], Str::replace($subjects, 'i', ''));
        $this->assertEquals(['ths', 's', 'a', 'string'], Str::replace($subjects, 'i', '', 1));

        // $search as array/Collection
        $this->assertEquals('th!! !! a string', Str::replace('this is a string', ['i', 's'], '!', 2));
        $this->assertEquals('th!! !! a string', Str::replace('this is a string', new Collection(['i', 's']), '!', 2));
        $this->assertEquals('th!! i! a string', Str::replace('this is a string', ['i', 's'], '!', [1, 2]));
        $this->assertEquals('th!! i! a !tring', Str::replace('this is a string', ['i', 's'], '!', [1]));

        // $search and $replace as array/Collection
        $this->assertEquals('th!? !? a string', Str::replace('this is a string', ['i', 's'], ['!', '?'], 2));
        $this->assertEquals('th! ! a string', Str::replace('this is a string', ['i', 's'], ['!'], 2));
        $this->assertEquals('th!? !? a string', Str::replace('this is a string', new Collection(['i', 's']), new Collection(['!', '?']), 2));
        $this->assertEquals('th!? !? a string', Str::replace('this is a string', new Collection(['i', 's']), ['!', '?'], 2));
        $this->assertEquals('th!? !? a string', Str::replace('this is a string', ['i', 's'], new Collection(['!', '?']), 2));
        $this->assertEquals('th!? !s a string', Str::replace('this is a string', ['i', 's'], ['!', '?'], [2, 1]));
        $this->assertEquals('th!s !s a string', Str::replace('this is a string', ['i', 's'], ['!', '?'], [2, 0]));
        $this->assertEquals('th!? !? a ?tring', Str::replace('this is a string', ['i', 's'], ['!', '?'], [2]));
        $this->assertEquals('th! ! a tring', Str::replace('this is a string', ['i', 's'], ['!'], [2]));
        $this->assertEquals('th! !s a string', Str::replace('this is a string', ['i', 's'], ['!'], [2, 1]));

        // replacement order
        $this->assertEquals('F', Str::replace('A', ['A', 'B', 'C', 'D', 'E'], ['B', 'C', 'D', 'E', 'F'], 1));
        $this->assertEquals('apearple p', Str::replace('a p', ['a', 'p'], ['apple', 'pear'], 1));
        $this->assertEquals('apearpearle p', Str::replace('a p', ['a', 'p'], ['apple', 'pear'], [1, 2]));
        $this->assertEquals('apearpearle pear', Str::replace('a p', ['a', 'p'], ['apple', 'pear'], [1, 3]));
    }

    public function testReplaceInvalid1()
    {
        $this->expectException('Exception');

        Str::replace('some string', 'string', ['array'], 1);
    }

    public function testReplaceInvalid2()
    {
        $this->expectException('Exception');

        Str::replace('some string', 'string', 'other string', 'some invalid string as limit');
    }

    public function testReplaceInvalid3()
    {
        $this->expectException('Exception');

        Str::replace('some string', ['some', 'string'], 'other string', [1, 'string']);
    }

    public function testReplacements()
    {
        // simple example
        $this->assertEquals([
            ['search' => 'a', 'replace' => 'b', 'limit' => 2]
        ], Str::replacements('a', 'b', 2));

        // multiple searches
        $this->assertEquals([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'c', 'limit' => 2]
        ], Str::replacements(['a', 'b'], 'c', 2));

        // multiple replacements
        $this->assertEquals([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'd', 'limit' => 2]
        ], Str::replacements(['a', 'b'], ['c', 'd'], 2));

        $this->assertEquals([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => '', 'limit' => 2]
        ], Str::replacements(['a', 'b'], ['c'], 2));

        // multiple limits
        $this->assertEquals([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'c', 'limit' => 1]
        ], Str::replacements(['a', 'b'], 'c', [2, 1]));

        $this->assertEquals([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'd', 'limit' => 1]
        ], Str::replacements(['a', 'b'], ['c', 'd'], [2, 1]));

        $this->assertEquals([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'd', 'limit' => -1]
        ], Str::replacements(['a', 'b'], ['c', 'd'], [2]));
    }

    public function testReplacementsInvalid()
    {
        $this->expectException('Exception');

        Str::replacements('string', ['array'], 1);
    }

    public function testReplaceReplacements()
    {
        $this->assertEquals(
            'other other string',
            Str::replaceReplacements('some some string', [
                [
                'search'  => 'some',
                'replace' => 'other',
                'limit'   => -1
                ]
            ])
        );

        $this->assertEquals(
            'other interesting string',
            Str::replaceReplacements('some some string', [
                [
                'search'  => 'some',
                'replace' => 'other',
                'limit'   => -1
                ],
                [
                'search'  => 'other string',
                'replace' => 'interesting string',
                'limit'   => 1
                ]
            ])
        );

        // edge cases are tested in the Str::replace() unit test
    }

    public function testReplaceReplacementsInvalid()
    {
        $this->expectException('Exception');

        Str::replaceReplacements('some string', [
            [
                'search'  => 'some',
                'replace' => 'other',
                'limit'   => 'string'
            ]
        ]);
    }

    public function testRtrim()
    {
        $this->assertEquals('test', Str::rtrim('test '));
        $this->assertEquals('test', Str::rtrim('test  '));
        $this->assertEquals('test', Str::rtrim('test.jpg', '.jpg'));
    }

    public function testShort()
    {
        $string = 'Super Äwesøme String';

        // too long
        $this->assertEquals('Super…', Str::short($string, 5));

        // not too long
        $this->assertEquals($string, Str::short($string, 100));

        // zero chars
        $this->assertEquals($string, Str::short($string, 0));

        // with different ellipsis character
        $this->assertEquals('Super---', Str::short($string, 5, '---'));

        // with null
        $this->assertEquals('', Str::short(null, 5));

        // with number
        $this->assertEquals(12345, Str::short(12345, 5));

        // with long number
        $this->assertEquals('12345…', Str::short(123456, 5));
    }

    public function testSlug()
    {
        // Double dashes
        $this->assertEquals('a-b', Str::slug('a--b'));

        // Dashes at the end of the line
        $this->assertEquals('a', Str::slug('a-'));

        // Dashes at the beginning of the line
        $this->assertEquals('a', Str::slug('-a'));

        // Underscores converted to dashes
        $this->assertEquals('a-b', Str::slug('a_b'));

        // Unallowed characters
        $this->assertEquals('a-b', Str::slug('a@b'));

        // Spaces characters
        $this->assertEquals('a-b', Str::slug('a b'));

        // Double Spaces characters
        $this->assertEquals('a-b', Str::slug('a  b'));

        // Custom separator
        $this->assertEquals('a+b', Str::slug('a-b', '+'));

        // Allow underscores
        $this->assertEquals('a_b', Str::slug('a_b', '-', 'a-z0-9_'));

        // store default defaults
        $defaults = Str::$defaults['slug'];

        // Custom str defaults
        Str::$defaults['slug']['separator'] = '+';
        Str::$defaults['slug']['allowed']   = 'a-z0-9_';

        $this->assertEquals('a+b', Str::slug('a-b'));
        $this->assertEquals('a_b', Str::slug('a_b'));

        // Reset str defaults
        Str::$defaults['slug'] = $defaults;
    }

    public function testSplit()
    {
        $string = 'ä,ö,ü,ß';
        $this->assertEquals(['ä', 'ö', 'ü', 'ß'], Str::split($string));

        $string = 'ä/ö/ü/ß';
        $this->assertEquals(['ä', 'ö', 'ü', 'ß'], Str::split($string, '/'));

        $string = 'ää/ö/üü/ß';
        $this->assertEquals(['ää', 'üü'], Str::split($string, '/', 2));
    }

    public function testStartsWith()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertTrue(Str::startsWith($string, ''));
        $this->assertTrue(Str::startsWith($string, 'H'));
        $this->assertFalse(Str::startsWith($string, 'h'));
        $this->assertTrue(Str::startsWith($string, 'Hellö'));
        $this->assertFalse(Str::startsWith($string, 'hellö'));

        // case insensitive
        $this->assertTrue(Str::startsWith($string, '', true));
        $this->assertTrue(Str::startsWith($string, 'H', true));
        $this->assertTrue(Str::startsWith($string, 'h', true));
        $this->assertTrue(Str::startsWith($string, 'Hellö', true));
        $this->assertTrue(Str::startsWith($string, 'hellö', true));
    }

    public function testSubstr()
    {
        $string = 'äöü';

        $this->assertEquals($string, Str::substr($string));
        $this->assertEquals($string, Str::substr($string, 0));
        $this->assertEquals($string, Str::substr($string, 0, 3));
        $this->assertEquals('ä', Str::substr($string, 0, 1));
        $this->assertEquals('äö', Str::substr($string, 0, 2));
        $this->assertEquals('ü', Str::substr($string, -1));
    }

    public function testTemplate()
    {
        $string = 'From {{ b }} to {{ a }}';
        $this->assertEquals('From here to there', Str::template($string, ['a' => 'there', 'b' => 'here']));
        $this->assertEquals('From  to ', Str::template($string, []));
        $this->assertEquals('From - to -', Str::template($string, [], '-'));
    }

    public function testTemplateWithString()
    {
        $template = Str::template('Hello {{ user }}', [
            'user' => 'homer'
        ]);

        $this->assertEquals('Hello homer', $template);
    }

    public function testTemplateWithArray()
    {
        $template = Str::template('Hello {{ user.username }}', [
            'user' => [
                'username' => 'homer'
            ]
        ]);

        $this->assertEquals('Hello homer', $template);
    }

    public function testTemplateWithObject()
    {
        $template = Str::template('Hello {{ user.username }}', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('Hello homer', $template);
    }

    public function testTemplateWithObjectMethod()
    {
        $template = Str::template('{{ user.username }} says: {{ user.says("hi") }}', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('homer says: hi', $template);
    }

    public function testTrim()
    {
        $this->assertEquals('test', Str::trim(' test '));
        $this->assertEquals('test', Str::trim('  test  '));
        $this->assertEquals('test', Str::trim('.test.', '.'));
    }

    public function testUcfirst()
    {
        $this->assertEquals('Hello world', Str::ucfirst('hello world'));
        $this->assertEquals('Hello world', Str::ucfirst('Hello World'));
    }

    public function testUcwords()
    {
        $this->assertEquals('Hello World', Str::ucwords('hello world'));
        $this->assertEquals('Hello World', Str::ucwords('Hello world'));
        $this->assertEquals('Hello World', Str::ucwords('HELLO WORLD'));
    }

    public function testUntil()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertEquals('Hellö', Str::until($string, 'ö'));
        $this->assertEquals(false, Str::until($string, 'Ö'));
        $this->assertEquals(false, Str::until($string, 'x'));

        // case insensitive
        $this->assertEquals('Hellö', Str::until($string, 'ö', true));
        $this->assertEquals('Hellö', Str::until($string, 'Ö', true));
        $this->assertEquals(false, Str::until($string, 'x'));
    }

    public function testUpper()
    {
        $this->assertEquals('ÖÄÜ', Str::upper('öäü'));
        $this->assertEquals('ÖÄÜ', Str::upper('Öäü'));
    }
}
