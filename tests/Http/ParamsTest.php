<?php

namespace Kirby\Http;

use Kirby\TestCase;

class ParamsTest extends TestCase
{
	public function testConstructWithArray(): void
	{
		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('value-a', $params->a);
		$this->assertSame('value-b', $params->b);
	}

	public function testConstructWithString(): void
	{
		$params = new Params('a:value-a/b:value-b');

		$this->assertSame('value-a', $params->a);
		$this->assertSame('value-b', $params->b);
	}

	public function testConstructWithEmptyValue(): void
	{
		$params = new Params('a:/b:');

		$this->assertNull($params->a);
		$this->assertNull($params->b);
	}

	public function testConstructWithSpecialChars(): void
	{
		$params = new Params(
			'a%2Fa%3A%20%3Ba%3F%22%3E:value-A%2FA%3A%20%3BA%3F%22%3E/' .
			'b%2Fb%3A%20%3Bb%3F%22%3E:value-B%2FB%3A%20%3BB%3F%22%3E'
		);

		$this->assertSame('value-A/A: ;A?">', $params->{'a/a: ;a?">'});
		$this->assertSame('value-B/B: ;B?">', $params->{'b/b: ;b?">'});
	}

	public function testExtractFromNull(): void
	{
		$params   = Params::extract();
		$expected = [
			'path'   => null,
			'params' => null,
			'slash'  => false
		];

		$this->assertSame($expected, $params);
	}

	public function testExtractFromEmptyString(): void
	{
		$params   = Params::extract('');
		$expected = [
			'path'   => null,
			'params' => null,
			'slash'  => false
		];

		$this->assertSame($expected, $params);
	}

	public function testExtractFromZeroString(): void
	{
		$params   = Params::extract('price:0');
		$expected = ['price' => '0'];

		$this->assertSame($expected, $params['params']);
	}

	public function testExtractFromSeparator(): void
	{
		$params   = Params::extract(Params::separator());
		$expected = [
			'path'   => [],
			'params' => [],
			'slash'  => false
		];

		$this->assertSame($expected, $params);
	}

	public function testToString(): void
	{
		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('a:value-a/b:value-b', $params->toString());
	}

	public function testToStringWithLeadingSlash(): void
	{
		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('/a:value-a/b:value-b', $params->toString(true));
	}

	public function testToStringWithTrailingSlash(): void
	{
		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('a:value-a/b:value-b/', $params->toString(false, true));
	}

	public function testToStringWithWindowsSeparator(): void
	{
		Params::$separator = ';';

		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('a;value-a/b;value-b/', $params->toString(false, true));

		Params::$separator = null;
	}

	public function testToStringWithSpecialChars(): void
	{
		$params = new Params([
			'a/a: ;a?">' => 'value-A/A: ;A?">',
			'b/b: ;b?">' => 'value-B/B: ;B?">',
		]);

		$this->assertSame(
			'a%2Fa%3A%20%3Ba%3F%22%3E:value-A%2FA%3A%20%3BA%3F%22%3E/' .
			'b%2Fb%3A%20%3Bb%3F%22%3E:value-B%2FB%3A%20%3BB%3F%22%3E',
			$params->toString()
		);
	}

	public function testUnsetParam(): void
	{
		$params = new Params(['foo' => 'bar']);
		$params->foo = null;

		$this->assertSame('', $params->toString());
	}
}
