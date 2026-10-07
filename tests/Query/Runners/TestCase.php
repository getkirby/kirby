<?php

namespace Kirby\Query\Runners;

use Kirby\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	public static function resultProvider(): array
	{
		return [
			'field' => [
				'user.name', // query
				['user' => ['name' => 'Homer']], // context
				'Homer', // result
			],

			'field starting with a keyword' => [
				'a.nullable', // query
				['a' => ['nullable' => 'n']], // context
				'n', // result
			],

			'nested field' => [
				'user.name.first', // query
				['user' => ['name' => ['first' => 'Homer']]], // context
				'Homer' // result
			],

			'subscript notation with string as key' => [
				'this["my.key"]["another key"]', // query
				['my.key' => ['another key' => 'yes']], // context
				'yes' // result
			],

			'subscript notation with expression as key' => [
				'this["my.key"][page.id]', // query
				[
					'my.key' => ['another key' => 'yes'],
					'page'   => ['id' => 'another key']
				], // context
				'yes' // result
			],

			'string argument keeps backslashes' => [
				'a.f("say \"hi\"", \'\d+\')', // query
				['a' => ['f' => fn ($a, $b) => $a . ' ' . $b]], // context
				'say "hi" \d+', // result
			],

			'method result' => [
				'user.get("arg").thing', // query
				['user' => ['get' => fn ($a) => ['thing' => $a]]], // context
				'arg' // result
			],

			'array value with arguments is not called' => [
				'a.k("x")', // query
				['a' => ['k' => 'strtoupper']], // context
				'strtoupper', // result
			],

			'closure access to parent context' => [
				'thing.call(() => result).field', // query
				[
					'result' => ['field' => 42],
					'thing'  => ['call' => fn ($callback) => $callback()]
				], // context
				42 // result
			],

			'function result for explicit global function' => [
				'foo(42).bar', // query
				[], // context
				84, // result
				['foo' => fn ($a) => ['bar' => $a * 2]] // functions
			],

			'global function result when function looks like variable - i' => [
				'foo.bar', // query
				[], // context
				42, // result
				['foo' => fn () => ['bar' => 42]] // functions
			],

			'null context entry over global function' => [
				'page?.title', // query
				['page' => null], // context
				null, // result
				['page' => fn (string $id) => ['title' => $id]] // functions
			],

			'equal comparison' => [
				'5 == 5', // query
				[], // context
				true, // result
			],

			'strict equal comparison' => [
				'5 === 5', // query
				[], // context
				true, // result
			],

			'not equal comparison' => [
				'5 != 3', // query
				[], // context
				true, // result
			],

			'strict not equal comparison' => [
				'5 !== "5"', // query
				[], // context
				true, // result
			],

			'greater than comparison' => [
				'5 > 3', // query
				[], // context
				true, // result
			],

			'less than comparison' => [
				'3 < 5', // query
				[], // context
				true, // result
			],

			'greater than or equal comparison' => [
				'5 >= 5', // query
				[], // context
				true, // result
			],

			'less than or equal comparison' => [
				'5 <= 5', // query
				[], // context
				true, // result
			],

			'comparison with variables' => [
				'a > b', // query
				['a' => 10, 'b' => 3], // context
				true, // result
			],

			'comparison with member access' => [
				'user.age > user.minAge', // query
				['user' => ['age' => 25, 'minAge' => 18]], // context
				true, // result
			],

			// Logical operations
			'logical AND' => [
				'true && true', // query
				[], // context
				true, // result
			],

			'logical OR' => [
				'false || true', // query
				[], // context
				true, // result
			],

			'logical AND keyword' => [
				'a AND b', // query
				['a' => true, 'b' => true], // context
				true, // result
			],

			'logical OR keyword' => [
				'a OR b', // query
				['a' => false, 'b' => true], // context
				true, // result
			],

			'uppercase keyword inside a string' => [
				'a == "ORANGE"', // query
				['a' => 'ORANGE'], // context
				true, // result
			],

			'complex logical expression' => [
				'(a > b) && (c || d)', // query
				[
					'a' => 10,
					'b' => 3,
					'c' => false,
					'd' => true
				], // context
				true, // result
			],

			'logical precedence' => [
				'true || false && false', // query
				[], // context
				true, // result
			],

			'logical operations with member access' => [
				'user.isAdmin && user.hasPermission', // query
				['user' => ['isAdmin' => true, 'hasPermission' => true]], // context
				true, // result
			],

			'logical NOT' => [
				'!false', // query
				[], // context
				true, // result
			],

			'logical NOT with truthy value' => [
				'!"foo"', // query
				[], // context
				false, // result
			],

			'logical NOT with member access' => [
				'!user.isAdmin', // query
				['user' => ['isAdmin' => false]], // context
				true, // result
			],

			'logical NOT with grouping' => [
				'!(a && b)', // query
				['a' => true, 'b' => false], // context
				true, // result
			],

			'logical NOT combined with AND' => [
				'a && !b', // query
				['a' => true, 'b' => false], // context
				true, // result
			],

			'logical NOT with ternary' => [
				'!user.isAdmin ? "guest" : "admin"', // query
				['user' => ['isAdmin' => true]], // context
				'admin', // result
			],

			// Short-circuit: the unused operand must not be resolved
			'coalesce skips the right side' => [
				'a ?? b.c', // query
				['a' => 'A', 'b' => null], // context
				'A', // result
			],

			'ternary default skips the false side' => [
				'a ?: b.c', // query
				['a' => 'A', 'b' => null], // context
				'A', // result
			],

			'ternary skips the false side' => [
				'a ? "T" : b.c', // query
				['a' => true, 'b' => null], // context
				'T', // result
			],

			'ternary skips the true side' => [
				'a ? b.c : "F"', // query
				['a' => false, 'b' => null], // context
				'F', // result
			],

			'logical AND skips the right side' => [
				'a && a.b', // query
				['a' => null], // context
				false, // result
			],

			'logical OR skips the right side' => [
				'a || b.c', // query
				['a' => true, 'b' => null], // context
				true, // result
			],

			// Arithmetic operations
			'basic addition' => [
				'2 + 3', // query
				[], // context
				5, // result
			],

			'basic subtraction' => [
				'5 - 3', // query
				[], // context
				2, // result
			],

			'subtraction without whitespace' => [
				'a-1', // query
				['a' => 5], // context
				4, // result
			],

			'negative variable' => [
				'-a', // query
				['a' => 2.5], // context
				-2.5, // result
			],

			'negative grouping' => [
				'-(1 + 2)', // query
				[], // context
				-3, // result
			],

			'negative argument' => [
				'a.f(-1)', // query
				['a' => ['f' => fn ($x) => $x]], // context
				-1, // result
			],

			'basic multiplication' => [
				'4 * 3', // query
				[], // context
				12, // result
			],

			'basic division' => [
				'10 / 2', // query
				[], // context
				5, // result
			],

			'basic modulo' => [
				'7 % 3', // query
				[], // context
				1, // result
			],

			'arithmetic with variables' => [
				'a + b', // query
				['a' => 10, 'b' => 3], // context
				13, // result
			],

			'arithmetic precedence' => [
				'2 + 3 * 4', // query
				[], // context
				14, // result (2 + (3 * 4) = 2 + 12 = 14)
			],

			'arithmetic with member access' => [
				'user.age + user.bonus', // query
				['user' => ['age' => 25, 'bonus' => 5]], // context
				30, // result
			],

			'complex arithmetic expression' => [
				'(x + y) * z', // query
				['x' => 10, 'y' => 3, 'z' => 2], // context
				26, // result
			],
		];
	}


	public static function interceptProvider(): array
	{
		return [
			'field' => [
				'user.name', // query
				['user' => $user = ['name' => 'Homer']], // context
				[$user], // intercept
			],

			'nested field' => [
				'user.name.first', // query
				[
					'user' => $user = [
						'name' => $name = ['first' => 'Homer']
					]
				], // context
				[$user, $name] // intercept
			],

			'method result' => (function () {
				$closureResult = ['age' => 42];
				$user = ['get' => fn () => $closureResult];

				return [
					'user.get("arg").age', // query
					['user' => $user], // context
					[$user, $closureResult] // intercept
				];
			})(),

			'closure result' => (function () {
				$result = ['field' => 'value'];
				$thing = ['call' => fn ($callback) => $callback()];

				return [
					'thing.call(() => result).field', // query
					['thing' => $thing, 'result' => $result], // context
					[$thing, $result] // intercept
				];
			})(),

			'function result for explicit global function' => (function () {
				$result = ['bar' => 'baz'];
				$functions = ['foo' => fn () => $result];

				return [
					'foo("arg").bar', // query
					[], // context
					[$result], // intercept
					$functions // functions
				];
			})(),

			'global function result when function looks like variable - a' => (function () {
				$result    = ['bar' => 'baz'];
				$functions = ['foo' => fn () => $result];

				return [
					'foo.bar', // query
					[], // context
					[$result], // intercept
					$functions // functions
				];
			})()
		];
	}
}
