<?php

namespace Kirby\Query;

use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

/**
 * @todo Deprecate in v6
 */
#[CoversClass(Segments::class)]
class SegmentsTest extends TestCase
{
	public function testFactory(): void
	{
		$segments = Segments::factory('a.b.c');
		$this->assertCount(5, $segments);

		$segments = Segments::factory('a().b(foo.bar).c(homer.simpson(2))');
		$this->assertCount(5, $segments);
		$this->assertSame('c', $segments->nth(4)->method);
		$this->assertCount(1, $segments->nth(2)->arguments);
		$this->assertSame(1, $segments->nth(2)->position);

		$segments = Segments::factory('user0.profiles1.mastodon');
		$this->assertCount(5, $segments);
		$this->assertSame(2, $segments->nth(4)->position);
	}

	public static function parseProvider(): array
	{
		return [
			[
				'foo.bar(homer.simpson)?.url',
				['foo', '.', 'bar(homer.simpson)', '?.', 'url']
			],
			[
				'user.check("gin", "tonic", user.array("gin", "tonic").args)',
				['user', '.', 'check("gin", "tonic", user.array("gin", "tonic").args)']
			],
			[
				'a().b(foo.bar)?.c(homer.simpson(2))',
				['a()', '.', 'b(foo.bar)', '?.', 'c(homer.simpson(2))']
			],
			[
				'foo.bar(() => foo.homer?.url).foo?.bar',
				['foo', '.', 'bar(() => foo.homer?.url)', '.', 'foo', '?.', 'bar']
			]
		];
	}

	#[DataProvider('parseProvider')]
	public function testParse(string $string, array $result): void
	{
		$segments = Segments::parse($string);
		$this->assertSame($result, $segments);
	}

	public function testResolveNestedArray1Level(): void
	{
		$segments = Segments::factory('user.username');
		$data  = [
			'user' => [
				'username' => 'homer'
			]
		];

		$this->assertSame('homer', $segments->resolve($data));
	}

	public function testResolveNestedNumericKeys(): void
	{
		$segments = Segments::factory('user.0');
		$data  = [
			'user' => [
				'homer',
				'marge'
			]
		];

		$this->assertSame('homer', $segments->resolve($data));

		$segments = Segments::factory('user.1');
		$this->assertSame('marge', $segments->resolve($data));
	}

	public function testResolveNestedArrayWithNumericMethods(): void
	{
		$segments = Segments::factory('user0.profiles1.mastodon');
		$data  = [
			'user0' => [
				'profiles1' => [
					'mastodon' => '@homer'
				]
			]
		];

		$this->assertSame('@homer', $segments->resolve($data));
	}

	public function testResolveNestedArray2Levels(): void
	{
		$segments = Segments::factory('user.profiles.mastodon');
		$data  = [
			'user' => [
				'profiles' => [
					'mastodon' => '@homer'
				]
			]
		];

		$this->assertSame('@homer', $segments->resolve($data));
	}

	public static function scalarProvider(): array
	{
		return [
			['test', 'string'],
			[1, 'integer'],
			[1.1, 'float'],
			[true, 'boolean'],
			[false, 'boolean'],
		];
	}

	#[DataProvider('scalarProvider')]
	public function testResolveWithArrayScalarValue($scalar): void
	{
		$segments = Segments::factory('value');
		$data     = ['value' => $scalar];
		$this->assertSame($scalar, $segments->resolve($data));
	}

	#[DataProvider('scalarProvider')]
	public function testResolveWithArrayScalarValue2Level($scalar): void
	{
		$segments = Segments::factory('parent.value');
		$data     =  [
			'parent' => [
				'value' => $scalar
			]
		];
		$this->assertSame($scalar, $segments->resolve($data));
	}

	#[DataProvider('scalarProvider')]
	public function testResolveWithArrayScalarValueError($scalar, $type): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to method/property "method" on ' . $type);

		$segments = Segments::factory('value.method');
		$data     = ['value' => $scalar];
		$segments->resolve($data);
	}

	public function testResolveWithArrayNullValue(): void
	{
		$segments = Segments::factory('value');
		$data     = ['value' => null];
		$this->assertNull($segments->resolve($data));
	}

	public function testResolveWithArrayNullValueError(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to method/property "method" on null');

		$segments = Segments::factory('value.method');
		$data     = ['value' => null];
		$segments->resolve($data);
	}

	public function testResolveWithArrayCallClosure(): void
	{
		$segments = Segments::factory('closure("test")');
		$data     = ['closure' => fn ($arg) => strtoupper($arg)];
		$this->assertSame('TEST', $segments->resolve($data));
	}

	public function testResolveWithArrayCallError(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot access array element "editor" with arguments');

		$segments = Segments::factory('editor("test")');
		$data     = ['editor' => new TestUser()];
		$segments->resolve($data);
	}

	public function testResolveWithArrayMissingKey1(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to non-existing property "editor" on array');

		$segments = Segments::factory('editor');
		$segments->resolve();
	}

	public function testResolveWithArrayMissingKey2(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to non-existing property "editor" on array');

		$segments = Segments::factory('editor.username');
		$segments->resolve();
	}

	public function testResolveWithObject1Level(): void
	{
		$segments = Segments::factory('user.username');
		$data     = ['user' => new TestUser()];
		$this->assertSame('homer', $segments->resolve($data));
	}

	public function tesResolvetWithObject2Level(): void
	{
		$segments = Segments::factory('user.profiles.mastodon');
		$data     = ['user' => new TestUser()];
		$this->assertSame('@homer', $segments->resolve($data));
	}

	public function testResolveWithObjectProperty(): void
	{
		$obj = new stdClass();
		$obj->test = 'testtest';
		$segments = Segments::factory('obj.test');
		$this->assertSame('testtest', $segments->resolve(compact('obj')));
	}

	public function testResolveWithObjectPropertyCallError(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to non-existing method "test" on object');

		$obj = new stdClass();
		$obj->test = 'testtest';
		$segments = Segments::factory('obj.test(123)');
		$segments->resolve(compact('obj'));
	}

	public function testResolveWithObjectMethodWithInteger(): void
	{
		$segments = Segments::factory('user.age(12)');
		$data     = ['user' => new TestUser()];
		$this->assertSame(12, $segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithBoolean(): void
	{
		// true
		$segments = Segments::factory('user.isYello(true)');
		$data     = ['user' => new TestUser()];
		$this->assertTrue($segments->resolve($data));

		// false
		$segments = Segments::factory('user.isYello(false)');
		$data     = ['user' => new TestUser()];
		$this->assertFalse($segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithNull(): void
	{
		$segments = Segments::factory('user.brainDump(null)');
		$data     = ['user' => new TestUser()];
		$this->assertNull($segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithString(): void
	{
		// double quotes
		$segments = Segments::factory('user.says("hello world")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello world', $segments->resolve($data));

		// single quotes
		$segments = Segments::factory("user.says('hello world' )");
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello world', $segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithEmptyString(): void
	{
		// double quotes
		$segments = Segments::factory('user.says("")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('', $segments->resolve($data));

		// single quotes
		$segments = Segments::factory("user.says('' )");
		$data     = ['user' => new TestUser()];
		$this->assertSame('', $segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithStringEscape(): void
	{
		// double quotes
		$segments = Segments::factory('user.says("hello \" world")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello " world', $segments->resolve($data));

		// single quotes
		$segments = Segments::factory("user.says('hello \' world' )");
		$data     = ['user' => new TestUser()];
		$this->assertSame("hello ' world", $segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithMultipleArguments(): void
	{
		$segments = Segments::factory('user.says("hello", "world")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello : world', $segments->resolve($data));

		// with escaping
		$segments = Segments::factory('user.says("hello\"", "world")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello" : world', $segments->resolve($data));

		// with mixed quotes
		$segments = Segments::factory('user.says(\'hello\\\'\', "world\"")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello\' : world"', $segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithMultipleArgumentsAndComma(): void
	{
		$segments = Segments::factory('user.says("hello,", "world")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello, : world', $segments->resolve($data));

		// with escaping
		$segments = Segments::factory('user.says("hello,\"", "world")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello," : world', $segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithMultipleArgumentsAndDot(): void
	{
		$segments = Segments::factory('user.says("I like", "love.jpg")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('I like : love.jpg', $segments->resolve($data));

		// with escaping
		$segments = Segments::factory('user.says("I \" like", "love.\"jpg")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('I " like : love."jpg', $segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithTrickyCharacters(): void
	{
		$segments = Segments::factory("user.likes(['(', ',', ']', '[', ')']).self.brainDump('hello')");
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello', $segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithArray(): void
	{
		$segments = Segments::factory('user.self.check("gin", "tonic", ["gin", "tonic", "cucumber"])');
		$data     = ['user' => new TestUser()];
		$this->assertTrue($segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithObjectMethodAsParameter(): void
	{
		$segments = Segments::factory('user.self.check("gin", "tonic", user.drink)');
		$data     = ['user' => new TestUser()];
		$this->assertTrue($segments->resolve($data));
	}

	public function testResolveWithNestedMethodCall(): void
	{
		$segments = Segments::factory('user.check("gin", "tonic", user.array("gin", "tonic").args)');
		$data     = ['user' => new TestUser()];
		$this->assertTrue($segments->resolve($data));
	}

	public function testResolveWithObjectMethodWithObjectMethodAsParameterAndMoreLevels(): void
	{
		$segments = Segments::factory("user.likes([',']).likes(user.brainDump(['(', ',', ']', ')', '['])).self");
		$data     = ['user' => $user = new TestUser()];
		$this->assertSame($user, $segments->resolve($data));
	}

	public function testResolveWithObjectMissingMethod1(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to non-existing method/property "username" on object');

		$segments = Segments::factory('user.username');
		$data     = ['user' => new stdClass()];
		$segments->resolve($data);
	}

	public function testResolveWithObjectMissingMethod2(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to non-existing method "username" on object');

		$segments = Segments::factory('user.username(12)');
		$data     = ['user' => new stdClass()];
		$segments->resolve($data);
	}

	public function testResolveWithOptionalChaining(): void
	{
		$segments = Segments::factory('user?.says("hi")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hi', $segments->resolve($data));

		$segments = Segments::factory('user.nothing?.says("hi")');
		$data     = ['user' => new TestUser()];
		$this->assertNull($segments->resolve($data));

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to method/property "says" on null');

		$segments = Segments::factory('user.nothing.says("hi")');
		$segments->resolve($data);
	}
}
