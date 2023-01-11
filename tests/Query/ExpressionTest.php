<?php

namespace Kirby\Query;

use Kirby\Exception\LogicException;

/**
 * @coversDefaultClass Kirby\Query\Expression
 */
class ExpressionTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$expression = Expression::factory('a ? b : c');
		$this->assertSame(5, count($expression->parts));
		$this->assertInstanceOf(Argument::class, $expression->parts[0]);
		$this->assertIsString($expression->parts[1]);
		$this->assertInstanceOf(Argument::class, $expression->parts[2]);
		$this->assertIsString($expression->parts[3]);
		$this->assertInstanceOf(Argument::class, $expression->parts[4]);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryWithoutComparison()
	{
		$expression = Expression::factory('foo.bar(true).url');
		$this->assertInstanceOf(Segments::class, $expression);
	}

	public function providerParse(): array
	{
		return [
			[
				'a ? b : c',
				['a', '?', 'b', ':', 'c']
			],
			[
				'a ?: c',
				['a', '?:', 'c']
			],
			[
				'a ?? b',
				['a', '??', 'b']
			],
			[
				'a ?? b ?? c ?? d',
				['a', '??', 'b', '??', 'c', '??', 'd']
			],
			[
				'a ? "x ? y : z" : c',
				['a', '?', '"x ? y : z"', ':', 'c']
			],
			[
				'a ? (x ?: z) : c',
				['a', '?', '(x ?: z)', ':', 'c']
			],
			[
				'null ?? (null ?? null ?? (false ? "what" : 42)) ?? "no"',
				['null', '??', '(null ?? null ?? (false ? "what" : 42))', '??', '"no"']
			],
			[
				'page.cover.toFile?.url ?? page.image?.url',
				['page.cover.toFile?.url', '??', 'page.image?.url']
			]
		];
	}

	/**
	 * @covers ::parse
	 * @dataProvider providerParse
	 */
	public function testParse(string $expression, array $result)
	{
		$parts = Expression::parse($expression);
		$this->assertSame($result, $parts);
	}

	public function providerResolve(): array
	{
		return [
			['true ? "yes" : "no"', 'yes'],
			['false ? "yes" : "no"', 'no'],
			['0 ? "yes" : "no"', 'no'],
			['"yes" ?: "no"', 'yes'],
			['0 ?: "no"', 'no'],
			['null ?? null ?? null ?? "yes"', 'yes'],
			['"yes" ?? "no"', 'yes'],
			['null ?? (null ?? null ?? (false ? "what" : 42)) ?? "no"', 42.0],
		];
	}

	/**
	 * @covers ::resolve
	 * @dataProvider providerResolve
	 */
	public function testResolve(string $input, mixed $result)
	{
		$expression = Expression::factory($input);
		$this->assertSame($result, $expression->resolve());
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObject()
	{
		$expression = Expression::factory('user.isYello(true) ? user.says("me") : "you"');
		$data = ['user' => new TestUser()];
		$this->assertSame('me', $expression->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveIncompleteTernary()
	{
		$expression = Expression::factory('"a" ? "b"');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Query: Incomplete ternary operator (missing matching `? :`)');

		$expression->resolve();
	}
}
