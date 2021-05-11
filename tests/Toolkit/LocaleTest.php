<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\Locale
 */
class LocaleTest extends TestCase
{
    protected $locales = [];
    protected $localeSuffix;

    public function setUp(): void
    {
        // make a backup of the current locales
        $this->locales = Locale::get();

        // test which locale suffix the system supports
        setlocale(LC_ALL, 'de_DE.' . $this->localeSuffix);
        if (setlocale(LC_ALL, '0') === 'de_DE.' . $this->localeSuffix) {
            $this->localeSuffix = 'utf8';
        } else {
            $this->localeSuffix = 'UTF-8';
        }

        // now set a baseline
        setlocale(LC_ALL, 'C');
    }

    public function tearDown(): void
    {
        Locale::set($this->locales);
    }

    /**
     * @covers ::export
     * @covers ::supportedConstants
     */
    public function testExport()
    {
        // valid array
        $this->assertSame([
            'LC_ALL'     => 'test1',
            'LC_NUMERIC' => 'test2'
        ], Locale::export([
            LC_ALL     => 'test1',
            LC_NUMERIC => 'test2'
        ]));

        // with prepared string key
        $this->assertSame([
            'LC_TEST' => 'test'
        ], Locale::export([
            'LC_TEST' => 'test'
        ]));

        // unknown key
        $this->assertSame([
            1234 => 'test'
        ], Locale::export([
            1234 => 'test'
        ]));
    }

    /**
     * @covers ::get
     * @covers ::normalizeConstant
     * @covers ::supportedConstants
     */
    public function testGet()
    {
        // default case (all locales are set to the same value)
        $this->assertSame([LC_ALL => 'C'], Locale::get());
        $this->assertSame([LC_ALL => 'C'], Locale::get(LC_ALL));
        $this->assertSame([LC_ALL => 'C'], Locale::get('LC_ALL'));
        $this->assertSame('C', Locale::get(LC_NUMERIC));
        $this->assertSame('C', Locale::get('LC_NUMERIC'));

        // different locale values
        Locale::set([LC_NUMERIC => 'de_DE.' . $this->localeSuffix]);
        $this->assertSame($expected = [
            LC_COLLATE  => 'C',
            LC_CTYPE    => 'C',
            LC_MONETARY => 'C',
            LC_NUMERIC  => 'de_DE.' . $this->localeSuffix,
            LC_TIME     => 'C',
            LC_MESSAGES => 'C'
        ], Locale::get());
        $this->assertSame($expected, Locale::get(LC_ALL));
        $this->assertSame($expected, Locale::get('LC_ALL'));
        $this->assertSame('de_DE.' . $this->localeSuffix, Locale::get(LC_NUMERIC));
        $this->assertSame('C', Locale::get(LC_CTYPE));
        $this->assertSame('C', Locale::get('LC_CTYPE'));
    }

    /**
     * @covers ::get
     * @covers ::normalizeConstant
     * @covers ::supportedConstants
     */
    public function testGetInvalid1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid locale category "KIRBY_AWESOME_LOCALE"');

        Locale::get('KIRBY_AWESOME_LOCALE');
    }

    /**
     * @covers ::get
     * @covers ::normalizeConstant
     * @covers ::supportedConstants
     */
    public function testGetInvalid2()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Could not determine locale for category "987654321"');

        Locale::get(987654321);
    }

    /**
     * @covers ::normalize
     * @covers ::normalizeConstant
     */
    public function testNormalize()
    {
        // empty array
        $this->assertSame([], Locale::normalize([]));

        // array with different key types
        $this->assertSame([
            LC_ALL     => 'test1',
            LC_NUMERIC => 'test2',
            'TEST'     => 'test3'
        ], Locale::normalize([
            'LC_ALL'   => 'test1',
            LC_NUMERIC => 'test2',
            'TEST'     => 'test3'
        ]));

        // single string
        $this->assertSame([
            LC_ALL => 'test'
        ], Locale::normalize('test'));

        // invalid argument
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Locale must be string or array');
        Locale::normalize(123);
    }

    /**
     * @covers ::set
     */
    public function testSetString()
    {
        $this->assertSame('C', setlocale(LC_ALL, '0'));

        Locale::set('de_DE.' . $this->localeSuffix);
        $this->assertSame('de_DE.' . $this->localeSuffix, setlocale(LC_ALL, '0'));
    }

    /**
     * @covers ::set
     */
    public function testSetArray1()
    {
        $this->assertSame('C', setlocale(LC_ALL, '0'));

        Locale::set([
            'LC_ALL'   => 'de_AT.' . $this->localeSuffix,
            'LC_CTYPE' => 'de_DE.' . $this->localeSuffix,
            LC_NUMERIC => 'de_CH.' . $this->localeSuffix
        ]);
        $this->assertSame('de_DE.' . $this->localeSuffix, setlocale(LC_CTYPE, '0'));
        $this->assertSame('de_CH.' . $this->localeSuffix, setlocale(LC_NUMERIC, '0'));
        $this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_COLLATE, '0'));
    }

    /**
     * @covers ::set
     */
    public function testSetArray2()
    {
        $this->assertSame('C', setlocale(LC_ALL, '0'));

        Locale::set([
            'LC_CTYPE' => 'de_DE.' . $this->localeSuffix,
            LC_NUMERIC => 'de_CH.' . $this->localeSuffix,
            'LC_ALL'   => 'de_AT.' . $this->localeSuffix
        ]);
        $this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_CTYPE, '0'));
        $this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_NUMERIC, '0'));
        $this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_COLLATE, '0'));
    }
}
