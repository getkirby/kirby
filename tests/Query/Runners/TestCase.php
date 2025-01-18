<?php

namespace Kirby\Query\Runners;

use Kirby\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	public static function resultProvider()
	{
		return [
			'field' => [
				'user.name', // query
				['user' => ['name' => 'Homer']], // context
				'Homer', // result
			],

			'nested field' => [
				'user.name.first', // query
				['user' => ['name' => ['first' => 'Homer']]], // context
				'Homer' // result
			],

			'method result' => [
				'user.get("arg").thing', // query
				['user' => ['get' => fn ($a) => ['thing' => $a]]], // context
				'arg' // result
			],

			'closure access to parent context' => [
				'thing.call(() => result).field', // query
				['result' => ['field' => 42], 'thing' => ['call' => fn ($callback) => $callback()]], // context
				42 // result
			],

			'function result for explicit global function' => [
				'foo(42).bar', // query
				[], // context
				84, // result
				['foo' => fn ($a) => ['bar' => $a * 2]] // globalFunctions
			],

			'global function result when function looks like variable - i' => [
				'foo.bar', // query
				[], // context
				42, // result
				['foo' => fn () => ['bar' => 42]] // globalFunctions
			],
		];
	}


	public static function interceptProvider()
	{
		return [
			'field' => [
				'user.name', // query
				['user' => $user = ['name' => 'Homer']], // context
				[$user], // intercept
			],

			'nested field' => [
				'user.name.first', // query
				['user' => $user = ['name' => $name = ['first' => 'Homer']]], // context
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
				$functionResult = ['bar' => 'baz'];
				$globalFunctions = ['foo' => fn () => $functionResult];

				return [
					'foo("arg").bar', // query
					[], // context
					[$functionResult], // intercept
					$globalFunctions // globalFunctions
				];
			})(),

			'global function result when function looks like variable - a' => (function () {
				$functionResult = ['bar' => 'baz'];
				$globalFunctions = ['foo' => fn () => $functionResult];

				return [
					'foo.bar', // query
					[], // context
					[$functionResult], // intercept
					$globalFunctions // globalFunctions
				];
			})()
		];
	}
}
