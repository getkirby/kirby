<?php

namespace Kirby\Toolkit;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Locale::class)]
class LocaleTest extends TestCase
{
	protected array $locales = [];
	protected string $localeSuffix = '';

	public function setUp(): void
	{
		// make a backup of the current locales
		$this->locales = Locale::get();

		// test which locale suffix the system supports
		setlocale(LC_ALL, 'de_DE.' . $this->localeSuffix);

		$this->localeSuffix = match (
			setlocale(LC_ALL, '0') === 'de_DE.' . $this->localeSuffix
		) {
			true => 'utf8',
			false => 'UTF-8'
		};

		// now set a baseline
		setlocale(LC_ALL, 'C');
		locale_set_default('en-US');
	}

	public function tearDown(): void
	{
		Locale::set($this->locales);
	}

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

	public function testGetInvalid1()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid locale category "KIRBY_AWESOME_LOCALE"');

		Locale::get('KIRBY_AWESOME_LOCALE');
	}

	public function testGetInvalid2()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Could not determine locale for category "987654321"');

		Locale::get(987654321);
	}

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
		$this->assertSame([LC_ALL => 'test'], Locale::normalize('test'));

		// invalid argument
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Locale must be string or array');
		Locale::normalize(123);
	}

	public function testSetString()
	{
		$this->assertSame('C', setlocale(LC_ALL, '0'));
		$this->assertSame('en-US', locale_get_default());

		Locale::set('de_DE.' . $this->localeSuffix);
		$this->assertSame('de_DE.' . $this->localeSuffix, setlocale(LC_ALL, '0'));
		$this->assertSame('de_DE.' . $this->localeSuffix, locale_get_default());
	}

	public function testSetArray1()
	{
		$this->assertSame('C', setlocale(LC_ALL, '0'));
		$this->assertSame('en-US', locale_get_default());

		Locale::set([
			'LC_ALL'   => 'de_AT.' . $this->localeSuffix,
			'LC_CTYPE' => 'de_DE.' . $this->localeSuffix,
			LC_NUMERIC => 'de_CH.' . $this->localeSuffix
		]);
		$this->assertSame('de_DE.' . $this->localeSuffix, setlocale(LC_CTYPE, '0'));
		$this->assertSame('de_CH.' . $this->localeSuffix, setlocale(LC_NUMERIC, '0'));
		$this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_COLLATE, '0'));
		$this->assertSame('de_AT.' . $this->localeSuffix, locale_get_default());
	}

	public function testSetArray2()
	{
		$this->assertSame('C', setlocale(LC_ALL, '0'));
		$this->assertSame('en-US', locale_get_default());

		Locale::set([
			'LC_CTYPE' => 'de_DE.' . $this->localeSuffix,
			LC_NUMERIC => 'de_CH.' . $this->localeSuffix,
			'LC_ALL'   => 'de_AT.' . $this->localeSuffix,
			'LC_TIME'  => 'de_CH.' . $this->localeSuffix
		]);
		$this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_CTYPE, '0'));
		$this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_NUMERIC, '0'));
		$this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_COLLATE, '0'));
		$this->assertSame('de_CH.' . $this->localeSuffix, locale_get_default());
	}
}
