<?php

namespace Kirby\Image;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Svg::class)]
class SvgTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	protected function svg(string $attrs): Svg
	{
		return new Svg('<svg xmlns="http://www.w3.org/2000/svg" ' . $attrs . '></svg>');
	}

	public function testConstructWithInvalidSvg(): void
	{
		$svg = new Svg('not an svg');
		$this->assertNull($svg->width());
		$this->assertNull($svg->height());
		$this->assertNull($svg->viewBox());
		$this->assertSame(0.0, $svg->ratio());
		$this->assertFalse($svg->hasNaturalSize());
	}

	public function testFrom(): void
	{
		$svg = Svg::from(static::FIXTURES . '/dimensions/circle-abs.svg');
		$this->assertSame(35.0, $svg->width());
		$this->assertSame([0.0, 0.0, 50.0, 50.0], $svg->viewBox());

		$svg = Svg::from(static::FIXTURES . '/dimensions/missing.svg');
		$this->assertNull($svg->viewBox());
	}

	public static function hasNaturalSizeProvider(): array
	{
		return [
			'size'        => ['width="40" height="10"', true],
			'width'       => ['width="40" viewBox="0 0 20 10"', true],
			'height in em' => ['height="2em"', true],
			'percentages' => ['width="100%" height="100%" viewBox="0 0 20 10"', false],
			'viewBox'     => ['viewBox="0 0 20 10"', false],
			'no size info' => ['', false],
		];
	}

	#[DataProvider('hasNaturalSizeProvider')]
	public function testHasNaturalSize(string $attrs, bool $expected): void
	{
		$this->assertSame($expected, $this->svg($attrs)->hasNaturalSize());
	}

	public static function sizeProvider(): array
	{
		return [
			'size'             => ['width="40" height="10" viewBox="0 0 10 10"', 40.0, 10.0],
			'decimals'         => ['width="35.6" height="20.4"', 35.6, 20.4],
			'units'            => ['width="24px" height="2em"', 24.0, 2.0],
			'width + viewBox'  => ['width="40" viewBox="0 0 20 10"', 40.0, 20.0],
			'height + viewBox' => ['width="100%" height="25" viewBox="10 10 50 50"', 25.0, 25.0],
			'width only'       => ['width="40"', 40.0, null],
			'viewBox'          => ['viewBox="10 10 50.4 151.7"', 50.4, 151.7],
			'invalid sizes'    => ['width="0" height="-5" viewBox="0 0 20 10"', 20.0, 10.0],
			'auto'             => ['width="auto" height="auto"', null, null],
			'no size info'     => ['', null, null],
		];
	}

	#[DataProvider('sizeProvider')]
	public function testHeight(string $attrs, float|null $width, float|null $height): void
	{
		$this->assertSame($height, $this->svg($attrs)->height());
	}

	public static function ratioProvider(): array
	{
		return [
			'size'            => ['width="40" height="10"', 4.0],
			'size + viewBox'  => ['width="40" height="10" viewBox="0 0 10 10"', 4.0],
			'width + viewBox' => ['width="40" viewBox="0 0 20 10"', 2.0],
			'percentages'     => ['width="100%" height="100%" viewBox="0 0 400 100"', 4.0],
			'viewBox'         => ['viewBox="0 0 188 152"', 188 / 152],
			'decimal viewBox' => ['viewBox="0 0 2 1.5"', 2 / 1.5],
			'offset viewBox'  => ['viewBox="10 10 50 25"', 2.0],
			'width only'      => ['width="40"', 0.0],
			'no size info'    => ['', 0.0],
		];
	}

	#[DataProvider('ratioProvider')]
	public function testRatio(string $attrs, float $expected): void
	{
		$this->assertSame($expected, $this->svg($attrs)->ratio());
	}

	public static function viewBoxProvider(): array
	{
		return [
			'spaces'     => ['0 0 24 12', [0.0, 0.0, 24.0, 12.0]],
			'commas'     => ['0,0,24,12', [0.0, 0.0, 24.0, 12.0]],
			'mixed'      => [" -1, -2\n 24.5  12 ", [-1.0, -2.0, 24.5, 12.0]],
			'negative'   => ['0 0 -24 12', null],
			'incomplete' => ['0 0 24', null],
			'empty'      => ['', null],
		];
	}

	#[DataProvider('viewBoxProvider')]
	public function testViewBox(string $value, array|null $expected): void
	{
		$this->assertSame($expected, $this->svg('viewBox="' . $value . '"')->viewBox());
	}

	#[DataProvider('sizeProvider')]
	public function testWidth(string $attrs, float|null $width, float|null $height): void
	{
		$this->assertSame($width, $this->svg($attrs)->width());
	}
}
