<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\Str
 */
class StrTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Str::$language = [];
    }

    /**
     * @covers ::accepted
     */
    public function testAccepted()
    {
        $this->assertSame([
            ['quality' => 1.0, 'value' => 'image/jpeg'],
            ['quality' => 0.7, 'value' => 'image/png']
        ], Str::accepted('image/jpeg,  image/png;q=0.7'));
    }

    /**
     * @covers ::ascii
     */
    public function testAscii()
    {
        $this->assertSame('aouss', Str::ascii('äöüß'));
        $this->assertSame('Istanbul', Str::ascii('İstanbul'));
        $this->assertSame('istanbul', Str::ascii('i̇stanbul'));
        $this->assertSame('Nashata istorija', Str::ascii('Нашата история'));
    }

    /**
     * @covers ::after
     */
    public function testAfter()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertSame(' Wörld', Str::after($string, 'ö'));
        $this->assertSame('', Str::after($string, 'Ö'));
        $this->assertSame('', Str::after($string, 'x'));

        // case insensitive
        $this->assertSame(' Wörld', Str::after($string, 'ö', true));
        $this->assertSame(' Wörld', Str::after($string, 'Ö', true));
        $this->assertSame('', Str::after($string, 'x'));

        // non existing chars
        $this->assertSame('', Str::after('string', '.'), 'string with non-existing character should return false');
    }

    /**
     * @covers ::after
     */
    public function testAfterWithEmptyNeedle()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The needle must not be empty');
        Str::after('test', '');
    }

    /**
     * @covers ::before
     */
    public function testBefore()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertSame('Hell', Str::before($string, 'ö'));
        $this->assertSame('', Str::before($string, 'Ö'));
        $this->assertSame('', Str::before($string, 'x'));

        // case insensitive
        $this->assertSame('Hell', Str::before($string, 'ö', true));
        $this->assertSame('Hell', Str::before($string, 'Ö', true));
        $this->assertSame('', Str::before($string, 'x'));
    }

    /**
     * @covers ::before
     */
    public function testBeforeWithEmptyNeedle()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The needle must not be empty');
        Str::before('test', '');
    }

    /**
     * @covers ::between
     */
    public function testBetween()
    {
        $this->assertSame('trin', Str::between('string', 's', 'g'), 'string between s and g should be trin');
        $this->assertSame('', Str::between('string', 's', '.'), 'function with non-existing character should return false');
        $this->assertSame('', Str::between('string', '.', 'g'), 'function with non-existing character should return false');
    }

    /**
     * @covers ::contains
     */
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

        // empty needle
        $this->assertTrue(Str::contains($string, ''));
    }

    /**
     * @covers ::date
     */
    public function testDate()
    {
        $time = mktime(1, 1, 1, 1, 29, 2020);

        // default `date` handler
        $this->assertSame($time, Str::date($time));
        $this->assertSame('29.01.2020', Str::date($time, 'd.m.Y'));

        // `strftime` handler
        $this->assertSame($time, Str::date($time, null, 'strftime'));
        $this->assertSame('29.01.2020', Str::date($time, '%d.%m.%Y', 'strftime'));
    }

    /**
     * @covers ::convert
     */
    public function testConvert()
    {
        $source = 'ÖÄÜ';

        // same encoding
        $result = Str::convert($source, 'UTF-8');
        $this->assertSame('UTF-8', Str::encoding($source));
        $this->assertSame('UTF-8', Str::encoding($result));

        // different  encoding
        $result = Str::convert($source, 'ISO-8859-1');
        $this->assertSame('UTF-8', Str::encoding($source));
        $this->assertSame('ISO-8859-1', Str::encoding($result));
    }

    /**
     * @covers ::encode
     */
    public function testEncode()
    {
        $email = 'test@getkirby.com';
        $this->assertSame($email, Html::decode(Str::encode($email)));
    }

    /**
     * @covers ::encoding
     */
    public function testEncoding()
    {
        $this->assertSame('UTF-8', Str::encoding('ÖÄÜ'));
    }

    /**
     * @covers ::endsWith
     */
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

    /**
     * @covers ::excerpt
     */
    public function testExcerpt()
    {
        $string   = 'This is a long text<br>with some html';
        $expected = 'This is a long text with …';
        $result   = Str::excerpt($string, 27);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::excerpt
     */
    public function testExcerptWithoutChars()
    {
        $string   = 'This is a long text<br>with some html';
        $expected = 'This is a long text with some html';
        $result   = Str::excerpt($string);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::excerpt
     */
    public function testExcerptWithZeroLength()
    {
        $string = 'This is a long text with some html';
        $result = Str::excerpt($string, 0);

        $this->assertSame($string, $result);
    }

    /**
     * @covers ::excerpt
     */
    public function testExcerptWithoutStripping()
    {
        $string   = 'This is a long text<br>with some html';
        $expected = 'This is a long text<br>with …';
        $result   = Str::excerpt($string, 30, false);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::excerpt
     */
    public function testExcerptWithDifferentRep()
    {
        $string   = 'This is a long text<br>with some html';
        $expected = 'This is a long text with ...';
        $result   = Str::excerpt($string, 27, true, ' ...');

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::excerpt
     */
    public function testExcerptWithSpaces()
    {
        $string   = 'This is a long text   <br>with some html';
        $expected = 'This is a long text with …';
        $result   = Str::excerpt($string, 27);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::excerpt
     */
    public function testExcerptWithLineBreaks()
    {
        $string   = 'This is a long text ' . PHP_EOL . ' with some html';
        $expected = 'This is a long text with …';
        $result   = Str::excerpt($string, 27);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::excerpt
     */
    public function testExcerptWithUnicodeChars()
    {
        $string   = 'Hellö Wörld text<br>with söme htmäl';
        $expected = 'Hellö Wörld text …';
        $result   = Str::excerpt($string, 20);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::float
     */
    public function testFloat()
    {
        $this->assertSame('0', Str::float(false));
        $this->assertSame('0', Str::float(null));
        $this->assertSame('0', Str::float(0));
        $this->assertSame('0', Str::float('0'));
        $this->assertSame('1', Str::float(true));
        $this->assertSame('1', Str::float(1));
        $this->assertSame('1', Str::float('1'));
        $this->assertSame('1.1', Str::float(1.1));
        $this->assertSame('1.1', Str::float('1.1'));
        $this->assertSame('1.1', Str::float('1,1'));
        $this->assertSame('1.11', Str::float('1,11'));
        $this->assertSame('1.111', Str::float('1,111'));
        $this->assertSame('-1', Str::float(-1));
        $this->assertSame('-1.1', Str::float(-1.1));
        $this->assertSame('-1.11', Str::float('-1.11'));
        $this->assertSame('-1.111', Str::float('-1,111'));
        $this->assertSame('1000', Str::float('1000'));
        $this->assertSame('1000.00', Str::float('1000.00'));
        $this->assertSame('1000.00', Str::float('1000,00'));
        $this->assertSame('1000', Str::float('1000'));
        $this->assertSame('1000000.00', Str::float('1000000.00'));
        $this->assertSame('0.00000001', Str::float(0.00000001));
    }

    /**
     * @covers ::from
     */
    public function testFrom()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertSame('ö Wörld', Str::from($string, 'ö'));
        $this->assertSame('', Str::from($string, 'Ö'));
        $this->assertSame('', Str::from($string, 'x'));

        // case insensitive
        $this->assertSame('ö Wörld', Str::from($string, 'ö', true));
        $this->assertSame('ö Wörld', Str::from($string, 'Ö', true));
        $this->assertSame('', Str::from($string, 'x'));
    }

    /**
     * @covers ::from
     */
    public function testFromWithEmptyNeedle()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The needle must not be empty');
        Str::from('test', '');
    }

    /**
     * @covers ::kebab
     */
    public function testKebab()
    {
        $string = 'KingCobra';
        $this->assertSame('king-cobra', Str::kebab($string));

        $string = 'kingCobra';
        $this->assertSame('king-cobra', Str::kebab($string));
    }

    /**
     * @covers ::length
     */
    public function testLength()
    {
        $this->assertSame(0, Str::length(''));
        $this->assertSame(3, Str::length('abc'));
        $this->assertSame(3, Str::length('öäü'));
        $this->assertSame(6, Str::length('Aœ?_ßö'));
    }

    /**
     * @covers ::lower
     */
    public function testLower()
    {
        $this->assertSame('öäü', Str::lower('ÖÄÜ'));
        $this->assertSame('öäü', Str::lower('Öäü'));
    }

    /**
     * @covers ::ltrim
     */
    public function testLtrim()
    {
        $this->assertSame('test', Str::ltrim(' test'));
        $this->assertSame('test', Str::ltrim('  test'));
        $this->assertSame('jpg', Str::ltrim('test.jpg', 'test.'));
    }

    /**
     * @covers ::pool
     */
    public function testPool()
    {
        // alpha
        $string = Str::pool('alpha', false);
        $this->assertTrue(V::alpha($string));
        $this->assertSame(52, strlen($string));

        // alphaLower
        $string = Str::pool('alphaLower', false);
        $this->assertTrue(V::alpha($string));
        $this->assertSame(26, strlen($string));

        // alphaUpper
        $string = Str::pool('alphaUpper', false);
        $this->assertTrue(V::alpha($string));
        $this->assertSame(26, strlen($string));

        // num
        $string = Str::pool('num', false);
        $this->assertTrue(V::num($string));
        $this->assertSame(10, strlen($string));

        // alphaNum
        $string = Str::pool('alphaNum', false);
        $this->assertTrue(V::alphanum($string));
        $this->assertSame(62, strlen($string));

        // [alphaLower, num]
        $string = Str::pool(['alphaLower', 'num'], false);
        $this->assertTrue(V::alphanum($string));
        $this->assertSame(36, strlen($string));

        // string vs. array
        $this->assertIsString(Str::pool('alpha', false));
        $this->assertIsArray(Str::pool('alpha'));
    }

    /**
     * @covers ::position
     */
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

    /**
     * @covers ::position
     */
    public function testPositionWithEmptyNeedle()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The needle must not be empty');
        Str::position('test', '');
    }

    /**
     * @covers ::query
     */
    public function testQuery()
    {
        $result = Str::query('data.1', ['data' => ['foo', 'bar']]);
        $this->assertSame('bar', $result);
    }

    /**
     * @covers ::random
     */
    public function testRandom()
    {
        // choose a high length for a high probability of occurrence of a character of any type
        $length = 200;

        $this->assertMatchesRegularExpression('/^[[:alnum:]]+$/', Str::random());
        $this->assertIsString(Str::random());
        $this->assertSame($length, strlen(Str::random($length)));

        $this->assertMatchesRegularExpression('/^[[:alpha:]]+$/', Str::random($length, 'alpha'));

        $this->assertMatchesRegularExpression('/^[[:upper:]]+$/', Str::random($length, 'alphaUpper'));

        $this->assertMatchesRegularExpression('/^[[:lower:]]+$/', Str::random($length, 'alphaLower'));

        $this->assertMatchesRegularExpression('/^[[:digit:]]+$/', Str::random($length, 'num'));

        $this->assertFalse(Str::random($length, 'something invalid'));
    }

    /**
     * @covers ::replace
     */
    public function testReplace()
    {
        // simple strings with limits
        $this->assertSame('ths s a strng', Str::replace('this is a string', 'i', ''));
        $this->assertSame('this is a string', Str::replace('this is a string', 'i', '', 0));
        $this->assertSame('ths is a string', Str::replace('this is a string', 'i', '', 1));
        $this->assertSame('ths s a string', Str::replace('this is a string', 'i', '', 2));
        $this->assertSame('ths s a strng', Str::replace('this is a string', 'i', '', 3));
        $this->assertSame('ths s a strng', Str::replace('this is a string', 'i', '', 1000));
        $this->assertSame('th!s !s a string', Str::replace('this is a string', 'i', '!', 2));
        $this->assertSame('th?!s ?!s a string', Str::replace('this is a string', 'i', '?!', 2));
        $this->assertSame('that also is a string', Str::replace('this is a string', 'this', 'that also', 1));
        $this->assertSame('this is aeä string', Str::replace('this is ää string', 'ä', 'ae', 1));
        $this->assertSame('this is aeae string', Str::replace('this is ää string', 'ä', 'ae', 2));
        $this->assertSame('this is äa string', Str::replace('this is aa string', 'a', 'ä', 1));
        $this->assertSame('this is ää string', Str::replace('this is aa string', 'a', 'ä', 2));

        // $subject as array
        $this->assertSame(['ths', 's', 'a', 'strng'], Str::replace(['this', 'is', 'a', 'string'], 'i', ''));
        $this->assertSame(['this', 'is', 'a', 'string'], Str::replace(['this', 'is', 'a', 'string'], 'i', '', 0));
        $this->assertSame(['ths', 's', 'a', 'strng'], Str::replace(['this', 'is', 'a', 'string'], 'i', '', 1));
        $this->assertSame(['ths', 's', 'a', 'strng'], Str::replace(['this', 'is', 'a', 'striing'], 'i', ''));
        $this->assertSame(['this', 'is', 'a', 'striing'], Str::replace(['this', 'is', 'a', 'striing'], 'i', '', 0));
        $this->assertSame(['ths', 's', 'a', 'string'], Str::replace(['this', 'is', 'a', 'striing'], 'i', '', 1));
        $this->assertSame(['ths', 's', 'a', 'strng'], Str::replace(['this', 'is', 'a', 'striing'], 'i', '', 2));

        // $subject as Collection
        $subjects = new Collection(['this', 'is', 'a', 'striing']);
        $this->assertSame(['ths', 's', 'a', 'strng'], Str::replace($subjects, 'i', ''));
        $this->assertSame(['ths', 's', 'a', 'string'], Str::replace($subjects, 'i', '', 1));

        // $search as array/Collection
        $this->assertSame('th!! !! a string', Str::replace('this is a string', ['i', 's'], '!', 2));
        $this->assertSame('th!! !! a string', Str::replace('this is a string', new Collection(['i', 's']), '!', 2));
        $this->assertSame('th!! i! a string', Str::replace('this is a string', ['i', 's'], '!', [1, 2]));
        $this->assertSame('th!! i! a !tring', Str::replace('this is a string', ['i', 's'], '!', [1]));

        // $search and $replace as array/Collection
        $this->assertSame('th!? !? a string', Str::replace('this is a string', ['i', 's'], ['!', '?'], 2));
        $this->assertSame('th! ! a string', Str::replace('this is a string', ['i', 's'], ['!'], 2));
        $this->assertSame('th!? !? a string', Str::replace('this is a string', new Collection(['i', 's']), new Collection(['!', '?']), 2));
        $this->assertSame('th!? !? a string', Str::replace('this is a string', new Collection(['i', 's']), ['!', '?'], 2));
        $this->assertSame('th!? !? a string', Str::replace('this is a string', ['i', 's'], new Collection(['!', '?']), 2));
        $this->assertSame('th!? !s a string', Str::replace('this is a string', ['i', 's'], ['!', '?'], [2, 1]));
        $this->assertSame('th!s !s a string', Str::replace('this is a string', ['i', 's'], ['!', '?'], [2, 0]));
        $this->assertSame('th!? !? a ?tring', Str::replace('this is a string', ['i', 's'], ['!', '?'], [2]));
        $this->assertSame('th! ! a tring', Str::replace('this is a string', ['i', 's'], ['!'], [2]));
        $this->assertSame('th! !s a string', Str::replace('this is a string', ['i', 's'], ['!'], [2, 1]));

        // replacement order
        $this->assertSame('F', Str::replace('A', ['A', 'B', 'C', 'D', 'E'], ['B', 'C', 'D', 'E', 'F'], 1));
        $this->assertSame('apearple p', Str::replace('a p', ['a', 'p'], ['apple', 'pear'], 1));
        $this->assertSame('apearpearle p', Str::replace('a p', ['a', 'p'], ['apple', 'pear'], [1, 2]));
        $this->assertSame('apearpearle pear', Str::replace('a p', ['a', 'p'], ['apple', 'pear'], [1, 3]));
    }

    /**
     * @covers ::replace
     */
    public function testReplaceInvalid1()
    {
        $this->expectException('Exception');

        Str::replace('some string', 'string', ['array'], 1);
    }

    /**
     * @covers ::replace
     */
    public function testReplaceInvalid2()
    {
        $this->expectException('Exception');

        Str::replace('some string', 'string', 'other string', 'some invalid string as limit');
    }

    /**
     * @covers ::replace
     */
    public function testReplaceInvalid3()
    {
        $this->expectException('Exception');

        Str::replace('some string', ['some', 'string'], 'other string', [1, 'string']);
    }

    /**
     * @covers ::replacements
     */
    public function testReplacements()
    {
        // simple example
        $this->assertSame([
            ['search' => 'a', 'replace' => 'b', 'limit' => 2]
        ], Str::replacements('a', 'b', 2));

        // multiple searches
        $this->assertSame([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'c', 'limit' => 2]
        ], Str::replacements(['a', 'b'], 'c', 2));

        // multiple replacements
        $this->assertSame([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'd', 'limit' => 2]
        ], Str::replacements(['a', 'b'], ['c', 'd'], 2));

        $this->assertSame([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => '', 'limit' => 2]
        ], Str::replacements(['a', 'b'], ['c'], 2));

        // multiple limits
        $this->assertSame([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'c', 'limit' => 1]
        ], Str::replacements(['a', 'b'], 'c', [2, 1]));

        $this->assertSame([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'd', 'limit' => 1]
        ], Str::replacements(['a', 'b'], ['c', 'd'], [2, 1]));

        $this->assertSame([
            ['search' => 'a', 'replace' => 'c', 'limit' => 2],
            ['search' => 'b', 'replace' => 'd', 'limit' => -1]
        ], Str::replacements(['a', 'b'], ['c', 'd'], [2]));
    }

    /**
     * @covers ::replacements
     */
    public function testReplacementsInvalid()
    {
        $this->expectException('Exception');

        Str::replacements('string', ['array'], 1);
    }

    /**
     * @covers ::replaceReplacements
     */
    public function testReplaceReplacements()
    {
        $this->assertSame(
            'other other string',
            Str::replaceReplacements('some some string', [
                [
                    'search'  => 'some',
                    'replace' => 'other',
                    'limit'   => -1
                ]
            ])
        );

        $this->assertSame(
            'other interesting story',
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
                ],
                [
                    'search'  => 'string',
                    'replace' => 'story',
                    'limit'   => 5
                ]
            ])
        );

        // edge cases are tested in the Str::replace() unit test
    }

    /**
     * @covers ::replaceReplacements
     */
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

    /**
     * @covers ::rtrim
     */
    public function testRtrim()
    {
        $this->assertSame('test', Str::rtrim('test '));
        $this->assertSame('test', Str::rtrim('test  '));
        $this->assertSame('test', Str::rtrim('test.jpg', '.jpg'));
    }

    /**
     * @covers ::safeTemplate
     */
    public function testSafeTemplate()
    {
        $original = 'This is a {{ test }} with {< html >} and {{ normal }} text.';
        $expected = 'This is a awesome Test with <b>HTML</b> and &lt;b&gt;normal&lt;/b&gt; text.';

        $this->assertSame($expected, Str::safeTemplate($original, [
            'test'   => 'awesome Test',
            'html'   => '<b>HTML</b>',
            'normal' => '<b>normal</b>'
        ]));

        // fallback
        $this->assertSame('From - to -', Str::safeTemplate(
            'From {{ a }} to {{ b }}',
            [],
            ['fallback' => '-']
        ));

        // callback
        $data = [
            'test' => '<test>',
            'html' => '<html>'
        ];
        $this->assertSame('This is a &lt;test&gt; with <html>', Str::safeTemplate(
            'This is a {{ test }} with {< html >}',
            $data,
            ['callback' => true]
        ));
        $this->assertSame('This is a &lt;TEST&gt; with <HTML>', Str::safeTemplate(
            'This is a {{ test }} with {< html >}',
            $data,
            [
                'callback' => function ($result, $query, $callbackData) use ($data) {
                    $this->assertSame($data, $callbackData);
                    return strtoupper($result);
                }
            ]
        ));
    }

    /**
     * @covers ::short
     */
    public function testShort()
    {
        $string = 'Super Äwesøme String';

        // too long
        $this->assertSame('Super…', Str::short($string, 5));

        // not too long
        $this->assertSame($string, Str::short($string, 100));

        // zero chars
        $this->assertSame($string, Str::short($string, 0));

        // with different ellipsis character
        $this->assertSame('Super---', Str::short($string, 5, '---'));

        // with null
        $this->assertSame('', Str::short(null, 5));

        // with number
        $this->assertSame('12345', Str::short(12345, 5));

        // with long number
        $this->assertSame('12345…', Str::short(123456, 5));
    }

    /**
     * @covers ::slug
     */
    public function testSlug()
    {
        // Double dashes
        $this->assertSame('a-b', Str::slug('a--b'));

        // Dashes at the end of the line
        $this->assertSame('a', Str::slug('a-'));

        // Dashes at the beginning of the line
        $this->assertSame('a', Str::slug('-a'));

        // Underscores converted to dashes
        $this->assertSame('a-b', Str::slug('a_b'));

        // Unallowed characters
        $this->assertSame('a-b', Str::slug('a@b'));

        // Spaces characters
        $this->assertSame('a-b', Str::slug('a b'));

        // Double Spaces characters
        $this->assertSame('a-b', Str::slug('a  b'));

        // Custom separator
        $this->assertSame('a+b', Str::slug('a-b', '+'));

        // Allow underscores
        $this->assertSame('a_b', Str::slug('a_b', '-', 'a-z0-9_'));

        // store default defaults
        $defaults = Str::$defaults['slug'];

        // Custom str defaults
        Str::$defaults['slug']['separator'] = '+';
        Str::$defaults['slug']['allowed']   = 'a-z0-9_';

        $this->assertSame('a+b', Str::slug('a-b'));
        $this->assertSame('a_b', Str::slug('a_b'));

        // Reset str defaults
        Str::$defaults['slug'] = $defaults;

        // Language rules
        Str::$language = ['ä' => 'ae'];
        $this->assertSame('ae-b', Str::slug('ä b'));
        Str::$language = [];
    }

    /**
     * @covers ::snake
     */
    public function testSnake()
    {
        $string = 'KingCobra';
        $this->assertSame('king_cobra', Str::snake($string));

        $string = 'kingCobra';
        $this->assertSame('king_cobra', Str::snake($string));
    }

    /**
     * @covers ::split
     */
    public function testSplit()
    {
        // default separator
        $string = 'ä,ö,ü,ß';
        $this->assertSame(['ä', 'ö', 'ü', 'ß'], Str::split($string));

        // custom separator
        $string = 'ä/ö/ü/ß';
        $this->assertSame(['ä', 'ö', 'ü', 'ß'], Str::split($string, '/'));

        // custom separator and limited length
        $string = 'ää/ö/üü/ß';
        $this->assertSame(['ää', 'üü'], Str::split($string, '/', 2));

        // custom separator with line-breaks
        $string = <<<EOT
            ---
            -abc-
            ---
            -def-
EOT;
        $this->assertSame(['-abc-', '-def-'], Str::split($string, '---'));

        // input is already an array
        $string = ['ää', 'üü', 'ß'];
        $this->assertSame($string, Str::split($string));
    }

    /**
     * @covers ::startsWith
     */
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

    /**
     * @covers ::similarity
     */
    public function testSimilarity()
    {
        $this->assertSame([
            'matches' => 0,
            'percent' => 0.0
        ], Str::similarity('foo', 'bar'));

        $this->assertSame([
            'matches' => 0,
            'percent' => 0.0
        ], Str::similarity('foo', ''));

        $this->assertSame([
            'matches' => 0,
            'percent' => 0.0
        ], Str::similarity('', 'foo'));

        $this->assertSame([
            'matches' => 0,
            'percent' => 0.0
        ], Str::similarity('', ''));

        $this->assertSame([
            'matches' => 3,
            'percent' => 66.66666666666667
        ], Str::similarity('foo', 'fooBar'));

        $this->assertSame([
            'matches' => 3,
            'percent' => 100.0
        ], Str::similarity('foo', 'foo'));

        $this->assertSame([
            'matches' => 4,
            'percent' => 100.0
        ], Str::similarity('tête', 'tête'));

        $this->assertSame([
            'matches' => 3,
            'percent' => 75.0
        ], Str::similarity('Tête', 'tête'));

        $this->assertSame([
            'matches' => 0,
            'percent' => 0.0
        ], Str::similarity('foo', 'FOO'));

        $this->assertSame([
            'matches' => 1,
            'percent' => 20.0
        ], Str::similarity('Kirby', 'KIRBY'));

        // case-insensitive
        $this->assertSame([
            'matches' => 4,
            'percent' => 100.0
        ], Str::similarity('Tête', 'tête', true));

        $this->assertSame([
            'matches' => 2,
            'percent' => 66.66666666666667
        ], Str::similarity('foo', 'FOU', true));

        $this->assertSame([
            'matches' => 5,
            'percent' => 100.0
        ], Str::similarity('Kirby', 'KIRBY', true));
    }

    /**
     * @covers ::substr
     */
    public function testSubstr()
    {
        $string = 'äöü';

        $this->assertSame($string, Str::substr($string));
        $this->assertSame($string, Str::substr($string, 0));
        $this->assertSame($string, Str::substr($string, 0, 3));
        $this->assertSame('ä', Str::substr($string, 0, 1));
        $this->assertSame('äö', Str::substr($string, 0, 2));
        $this->assertSame('ü', Str::substr($string, -1));
    }

    /**
     * @covers ::template
     */
    public function testTemplate()
    {
        // query with a string
        $string = 'From {{ b }} to {{ a }}';
        $this->assertSame('From here to there', Str::template($string, ['a' => 'there', 'b' => 'here']));
        $this->assertSame('From {{ b }} to {{ a }}', Str::template($string, []));
        $this->assertSame('From here to {{ a }}', Str::template($string, ['b' => 'here']));
        $this->assertSame('From here to {{ a }}', Str::template($string, ['a' => null, 'b' => 'here']));
        $this->assertSame('From - to -', Str::template($string, [], ['fallback' => '-']));
        $this->assertSame('From  to ', Str::template($string, [], ['fallback' => '']));
        $this->assertSame('From here to -', Str::template($string, ['b' => 'here'], ['fallback' => '-']));

        // query with an array
        $template = Str::template('Hello {{ user.username }}', [
            'user' => [
                'username' => 'homer'
            ]
        ]);
        $this->assertSame('Hello homer', $template);

        $template = Str::template('{{ user.greeting }} {{ user.username }}', [
            'user' => [
                'username' => 'homer'
            ]
        ]);
        $this->assertSame('{{ user.greeting }} homer', $template);

        // query with an array and callback
        $template = Str::template('Hello {{ user.username }}', [
            'user' => [
                'username' => 'homer'
            ]
        ], [
            'callback' => fn ($result) => Str::ucfirst($result)
        ]);
        $this->assertSame('Hello Homer', $template);

        // query with an object
        $template = Str::template('Hello {{ user.username }}', [
            'user' => new QueryTestUser()
        ]);
        $this->assertSame('Hello homer', $template);

        $template = Str::template('{{ user.greeting }} {{ user.username }}', [
            'user' => new QueryTestUser()
        ]);
        $this->assertSame('{{ user.greeting }} homer', $template);

        // query with an object method
        $template = Str::template('{{ user.username }} says: {{ user.says("hi") }}', [
            'user' => new QueryTestUser()
        ]);
        $this->assertSame('homer says: hi', $template);

        $template = Str::template('{{ user.username }} says: {{ user.greeting("hi") }}', [
            'user' => new QueryTestUser()
        ]);
        $this->assertSame('homer says: {{ user.greeting("hi") }}', $template);
    }

    /**
     * @covers ::toBytes
     */
    public function testToBytes()
    {
        $this->assertSame(0, Str::toBytes(0));
        $this->assertSame(0, Str::toBytes(''));
        $this->assertSame(0, Str::toBytes(null));
        $this->assertSame(0, Str::toBytes(false));
        $this->assertSame(0, Str::toBytes('x'));
        $this->assertSame(0, Str::toBytes('K'));
        $this->assertSame(0, Str::toBytes('M'));
        $this->assertSame(0, Str::toBytes('G'));
        $this->assertSame(2, Str::toBytes(2));
        $this->assertSame(2, Str::toBytes('2'));
        $this->assertSame(2 * 1024, Str::toBytes('2K'));
        $this->assertSame(2 * 1024, Str::toBytes('2k'));
        $this->assertSame(2 * 1024 * 1024, Str::toBytes('2M'));
        $this->assertSame(2 * 1024 * 1024, Str::toBytes('2m'));
        $this->assertSame(2 * 1024 * 1024 * 1024, Str::toBytes('2G'));
        $this->assertSame(2 * 1024 * 1024 * 1024, Str::toBytes('2g'));
    }

    /**
     * @covers ::toType
     */
    public function testToType()
    {
        // string to string
        $this->assertSame('a', Str::toType('a', 'string'));

        // string to array
        $this->assertSame(['a'], Str::toType('a', 'array'));
        $this->assertSame(['a'], Str::toType('a', []));

        // string to bool
        $this->assertSame(true, Str::toType(true, 'bool'));
        $this->assertSame(true, Str::toType('true', 'bool'));
        $this->assertSame(true, Str::toType('true', 'boolean'));
        $this->assertSame(true, Str::toType(1, 'bool'));
        $this->assertSame(true, Str::toType('1', 'bool'));
        $this->assertSame(true, Str::toType('1', true));
        $this->assertFalse(Str::toType(false, 'bool'));
        $this->assertFalse(Str::toType('false', 'bool'));
        $this->assertFalse(Str::toType('false', 'boolean'));
        $this->assertFalse(Str::toType(0, 'bool'));
        $this->assertFalse(Str::toType('0', 'bool'));
        $this->assertFalse(Str::toType('0', false));

        // string to float
        $this->assertSame(1.1, Str::toType(1.1, 'float'));
        $this->assertSame(1.1, Str::toType('1.1', 'float'));
        $this->assertSame(1.1, Str::toType('1.1', 'double'));
        $this->assertSame(1.1, Str::toType('1.1', 1.1));

        // string to int
        $this->assertSame(1, Str::toType(1, 'int'));
        $this->assertSame(1, Str::toType('1', 'int'));
        $this->assertSame(1, Str::toType('1', 'integer'));
        $this->assertSame(1, Str::toType('1', 1));
    }

    /**
     * @covers ::trim
     */
    public function testTrim()
    {
        $this->assertSame('test', Str::trim(' test '));
        $this->assertSame('test', Str::trim('  test  '));
        $this->assertSame('test', Str::trim('.test.', '.'));
    }

    /**
     * @covers ::ucfirst
     */
    public function testUcfirst()
    {
        $this->assertSame('Hello world', Str::ucfirst('hello world'));
        $this->assertSame('Hello world', Str::ucfirst('Hello World'));
    }

    /**
     * @covers ::ucwords
     */
    public function testUcwords()
    {
        $this->assertSame('Hello World', Str::ucwords('hello world'));
        $this->assertSame('Hello World', Str::ucwords('Hello world'));
        $this->assertSame('Hello World', Str::ucwords('HELLO WORLD'));
    }

    /**
     * @covers ::unhtml
     */
    public function testUnhtml()
    {
        $string = 'some <em>crazy</em> stuff';
        $this->assertSame('some crazy stuff', Str::unhtml($string));
    }

    /**
     * @covers ::until
     */
    public function testUntil()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertSame('Hellö', Str::until($string, 'ö'));
        $this->assertSame('', Str::until($string, 'Ö'));
        $this->assertSame('', Str::until($string, 'x'));

        // case insensitive
        $this->assertSame('Hellö', Str::until($string, 'ö', true));
        $this->assertSame('Hellö', Str::until($string, 'Ö', true));
        $this->assertSame('', Str::until($string, 'x'));
    }

    /**
     * @covers ::until
     */
    public function testUntilWithEmptyNeedle()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The needle must not be empty');
        Str::until('test', '');
    }

    /**
     * @covers ::upper
     */
    public function testUpper()
    {
        $this->assertSame('ÖÄÜ', Str::upper('öäü'));
        $this->assertSame('ÖÄÜ', Str::upper('Öäü'));
    }

    /**
     * @covers ::widont
     */
    public function testWidont()
    {
        $this->assertSame('Test', Str::widont('Test'));
        $this->assertSame('Test?', Str::widont('Test?'));
        $this->assertSame('Test&nbsp;?', Str::widont('Test ?'));
        $this->assertSame('Test&nbsp;Headline', Str::widont('Test Headline'));
        $this->assertSame('Test Headline&nbsp;With&#8209;Dash', Str::widont('Test Headline With-Dash'));
        $this->assertSame('Test Headline&nbsp;With&#8209;Dash&nbsp;?', Str::widont('Test Headline With-Dash ?'));
        $this->assertSame('Omelette du&nbsp;fromage', Str::widont('Omelette du fromage'));
        $this->assertSame('Omelette du&nbsp;fromage.', Str::widont('Omelette du fromage.'));
        $this->assertSame('Omelette du&nbsp;fromage?', Str::widont('Omelette du fromage?'));
        $this->assertSame('Omelette du&nbsp;fromage&nbsp;?', Str::widont('Omelette du fromage ?'));
        $this->assertSame('', Str::widont());
    }
}
