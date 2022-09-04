<?php

namespace Kirby\Query;

use stdClass;

/**
 * @coversDefaultClass Kirby\Query\Segments
 */
class SegmentsTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$segments = Segments::factory('a.b.c');
		$this->assertSame(3, $segments->count());

		$segments = Segments::factory('a().b(foo.bar).c(homer.simpson(2))');
		$this->assertSame(3, $segments->count());
		$this->assertSame('c', $segments->nth(2)->method);
		$this->assertSame(1, $segments->nth(1)->arguments->count());
		$this->assertSame(1, $segments->nth(1)->position);

		$segments = Segments::factory('user0.profiles1.twitter');
		$this->assertSame(3, $segments->count());
		$this->assertSame(2, $segments->nth(2)->position);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveNestedArray1Level()
	{
		$segments = Segments::factory('user.username');
		$data  = [
			'user' => [
				'username' => 'homer'
			]
		];

		$this->assertSame('homer', $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveNestedNumericKeys()
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

	/**
	 * @covers ::resolve
	 */
	public function testResolveNestedArrayWithNumericMethods()
	{
		$segments = Segments::factory('user0.profiles1.twitter');
		$data  = [
			'user0' => [
				'profiles1' => [
					'twitter' => '@homer'
				]
			]
		];

		$this->assertSame('@homer', $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveNestedArray2Levels()
	{
		$segments = Segments::factory('user.profiles.twitter');
		$data  = [
			'user' => [
				'profiles' => [
					'twitter' => '@homer'
				]
			]
		];

		$this->assertSame('@homer', $segments->resolve($data));
	}

	public function scalarProvider(): array
	{
		return [
			['test', 'string'],
			[1, 'integer'],
			[1.1, 'float'],
			[true, 'boolean'],
			[false, 'boolean'],
		];
	}

	/**
	 * @covers ::resolve
	 * @dataProvider scalarProvider
	 */
	public function testResolveWithArrayScalarValue($scalar)
	{
		$segments = Segments::factory('value');
		$data     = ['value' => $scalar];
		$this->assertSame($scalar, $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 * @dataProvider scalarProvider
	 */
	public function testResolveWithArrayScalarValue2Level($scalar)
	{
		$segments = Segments::factory('parent.value');
		$data     =  [
			'parent' => [
				'value' => $scalar
			]
		];
		$this->assertSame($scalar, $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 * @dataProvider scalarProvider
	 */
	public function testResolveWithArrayScalarValueError($scalar, $type)
	{
		$this->expectException('Kirby\Exception\BadMethodCallException');
		$this->expectExceptionMessage('Access to method/property method on ' . $type);

		$segments = Segments::factory('value.method');
		$data     = ['value' => $scalar];
		$segments->resolve($data);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithArrayNullValue()
	{
		$segments = Segments::factory('value');
		$data     = ['value' => null];
		$this->assertNull($segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithArrayNullValueError()
	{
		$this->expectException('Kirby\Exception\BadMethodCallException');
		$this->expectExceptionMessage('Access to method/property method on null');

		$segments = Segments::factory('value.method');
		$data     = ['value' => null];
		$segments->resolve($data);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithArrayCallClosure()
	{
		$segments = Segments::factory('closure("test")');
		$data     = ['closure' => fn ($arg) => strtoupper($arg)];
		$this->assertSame('TEST', $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithArrayCallError()
	{
		$this->expectException('Kirby\Exception\InvalidArgumentException');
		$this->expectExceptionMessage('Cannot access array element user with arguments');

		$segments = Segments::factory('user("test")');
		$data     = ['user' => new TestUser()];
		$segments->resolve($data);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithArrayMissingKey1()
	{
		$this->expectException('Kirby\Exception\BadMethodCallException');
		$this->expectExceptionMessage('Access to non-existing property user on array');

		$segments = Segments::factory('user');
		$segments->resolve();
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithArrayMissingKey2()
	{
		$this->expectException('Kirby\Exception\BadMethodCallException');
		$this->expectExceptionMessage('Access to non-existing property user on array');

		$segments = Segments::factory('user.username');
		$segments->resolve();
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObject1Level()
	{
		$segments = Segments::factory('user.username');
		$data     = ['user' => new TestUser()];
		$this->assertSame('homer', $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function tesResolvetWithObject2Level()
	{
		$segments = Segments::factory('user.profiles.twitter');
		$data     = ['user' => new TestUser()];
		$this->assertSame('@homer', $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectProperty()
	{
		$obj = new stdClass();
		$obj->test = 'testtest';
		$segments = Segments::factory('obj.test');
		$this->assertSame('testtest', $segments->resolve(compact('obj')));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectPropertyCallError()
	{
		$this->expectException('Kirby\Exception\BadMethodCallException');
		$this->expectExceptionMessage('Access to non-existing method test on object');

		$obj = new stdClass();
		$obj->test = 'testtest';
		$segments = Segments::factory('obj.test(123)');
		$segments->resolve(compact('obj'));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithInteger()
	{
		$segments = Segments::factory('user.age(12)');
		$data     = ['user' => new TestUser()];
		$this->assertSame(12, $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithBoolean()
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

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithNull()
	{
		$segments = Segments::factory('user.brainDump(null)');
		$data     = ['user' => new TestUser()];
		$this->assertNull($segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithString()
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

	/**
	 * @covers ::resolve
	 */
	public function testREsolveWithObjectMethodWithEmptyString()
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

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithStringEscape()
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

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithMultipleArguments()
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

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithMultipleArgumentsAndComma()
	{
		$segments = Segments::factory('user.says("hello,", "world")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello, : world', $segments->resolve($data));

		// with escaping
		$segments = Segments::factory('user.says("hello,\"", "world")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello," : world', $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithMultipleArgumentsAndDot()
	{
		$segments = Segments::factory('user.says("I like", "love.jpg")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('I like : love.jpg', $segments->resolve($data));

		// with escaping
		$segments = Segments::factory('user.says("I \" like", "love.\"jpg")');
		$data     = ['user' => new TestUser()];
		$this->assertSame('I " like : love."jpg', $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithTrickyCharacters()
	{
		$segments = Segments::factory("user.likes(['(', ',', ']', '[', ')']).self.brainDump('hello')");
		$data     = ['user' => new TestUser()];
		$this->assertSame('hello', $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithArray()
	{
		$segments = Segments::factory('user.self.check("gin", "tonic", ["gin", "tonic", "cucumber"])');
		$data     = ['user' => new TestUser()];
		$this->assertTrue($segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithObjectMethodAsParameter()
	{
		$segments = Segments::factory('user.self.check("gin", "tonic", user.drink)');
		$data     = ['user' => new TestUser()];
		$this->assertTrue($segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithNestedMethodCall()
	{
		$segments = Segments::factory('user.check("gin", "tonic", user.array("gin", "tonic").args)');
		$data     = ['user' => new TestUser()];
		$this->assertTrue($segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMethodWithObjectMethodAsParameterAndMoreLevels()
	{
		$segments = Segments::factory("user.likes([',']).likes(user.brainDump(['(', ',', ']', ')', '['])).self");
		$data     = ['user' => $user = new TestUser()];
		$this->assertSame($user, $segments->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMissingMethod1()
	{
		$this->expectException('Kirby\Exception\BadMethodCallException');
		$this->expectExceptionMessage('Access to non-existing method/property username on object');

		$segments = Segments::factory('user.username');
		$data     = ['user' => new stdClass()];
		$segments->resolve($data);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithObjectMissingMethod2()
	{
		$this->expectException('Kirby\Exception\BadMethodCallException');
		$this->expectExceptionMessage('Access to non-existing method username on object');

		$segments = Segments::factory('user.username(12)');
		$data     = ['user' => new stdClass()];
		$segments->resolve($data);
	}
}
