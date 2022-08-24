<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class ParamsTest extends TestCase
{
	public function testConstructWithArray()
	{
		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('value-a', $params->a);
		$this->assertSame('value-b', $params->b);
	}

	public function testConstructWithString()
	{
		$params = new Params('a:value-a/b:value-b');

		$this->assertSame('value-a', $params->a);
		$this->assertSame('value-b', $params->b);
	}

	public function testConstructWithEmptyValue()
	{
		$params = new Params('a:/b:');

		$this->assertSame(null, $params->a);
		$this->assertSame(null, $params->b);
	}

	public function testConstructWithSpecialChars()
	{
		$params = new Params(
			'a%2Fa%3A%20%3Ba%3F%22%3E:value-A%2FA%3A%20%3BA%3F%22%3E/' .
			'b%2Fb%3A%20%3Bb%3F%22%3E:value-B%2FB%3A%20%3BB%3F%22%3E'
		);

		$this->assertSame('value-A/A: ;A?">', $params->{'a/a: ;a?">'});
		$this->assertSame('value-B/B: ;B?">', $params->{'b/b: ;b?">'});
	}

	public function testExtractFromNull()
	{
		$params   = Params::extract();
		$expected = [
			'path'   => null,
			'params' => null,
			'slash'  => false
		];

		$this->assertSame($expected, $params);
	}

	public function testExtractFromEmptyString()
	{
		$params   = Params::extract('');
		$expected = [
			'path'   => null,
			'params' => null,
			'slash'  => false
		];

		$this->assertSame($expected, $params);
	}

	public function testExtractFromSeparator()
	{
		$params   = Params::extract(Params::separator());
		$expected = [
			'path'   => [],
			'params' => [],
			'slash'  => false
		];

		$this->assertSame($expected, $params);
	}

	public function testToString()
	{
		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('a:value-a/b:value-b', $params->toString());
	}

	public function testToStringWithLeadingSlash()
	{
		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('/a:value-a/b:value-b', $params->toString(true));
	}

	public function testToStringWithTrailingSlash()
	{
		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('a:value-a/b:value-b/', $params->toString(false, true));
	}

	public function testToStringWithWindowsSeparator()
	{
		Params::$separator = ';';

		$params = new Params([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('a;value-a/b;value-b/', $params->toString(false, true));

		Params::$separator = null;
	}

	public function testToStringWithSpecialChars()
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

	public function testUnsetParam()
	{
		$params = new Params(['foo' => 'bar']);
		$params->foo = null;

		$this->assertSame('', $params->toString());
	}
}
