<?php

namespace Kirby\Toolkit;

use ArgumentCountError;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use TypeError;

/**
 * @coversDefaultClass \Kirby\Toolkit\Component
 */
class ComponentTest extends TestCase
{
	public function tearDown(): void
	{
		Component::$types  = [];
		Component::$mixins = [];
	}

	/**
	 * @covers ::__construct
	 * @covers ::__call
	 * @covers ::applyProps
	 */
	public function testProp()
	{
		Component::$types = [
			'test' => [
				'props' => [
					'prop' => fn ($prop) => $prop
				]
			]
		];

		$component = new Component('test', ['prop' => 'prop value']);

		$this->assertSame('prop value', $component->prop());
		$this->assertSame('prop value', $component->prop);
	}

	/**
	 * @covers ::applyProps
	 */
	public function testPropWithDefaultValue()
	{
		Component::$types = [
			'test' => [
				'props' => [
					'prop' => fn ($prop = 'default value') => $prop
				]
			]
		];

		$component = new Component('test');

		$this->assertSame('default value', $component->prop());
		$this->assertSame('default value', $component->prop);
	}

	/**
	 * @covers ::applyProps
	 */
	public function testPropWithFixedValue()
	{
		Component::$types = [
			'test' => [
				'props' => [
					'prop' => 'test'
				]
			]
		];

		$component = new Component('test');

		$this->assertSame('test', $component->prop());
		$this->assertSame('test', $component->prop);
	}

	/**
	 * @covers ::applyProps
	 */
	public function testPropWithInvalidValue()
	{
		Component::$types = [
			'test' => [
				'props' => [
					'prop' => fn (string $prop) => $prop
				]
			]
		];

		$this->expectException(TypeError::class);
		$this->expectExceptionMessage('Invalid value for "prop"');

		new Component('test', ['prop' => [1, 2, 3]]);
	}

	/**
	 * @covers ::applyProps
	 */
	public function testPropWithMissingValue()
	{
		Component::$types = [
			'test' => [
				'props' => [
					'prop' => fn (string $prop) => $prop
				]
			]
		];

		$this->expectException(ArgumentCountError::class);
		$this->expectExceptionMessage('Please provide a value for "prop"');

		new Component('test');
	}

	/**
	 * @covers ::__construct
	 */
	public function testAttrs()
	{
		Component::$types = [
			'test' => []
		];

		$component = new Component('test', ['foo' => 'bar']);

		$this->assertSame('bar', $component->foo());
		$this->assertSame('bar', $component->foo);
	}

	/**
	 * @covers ::__construct
	 * @covers ::__call
	 * @covers ::applyComputed
	 */
	public function testComputed()
	{
		Component::$types = [
			'test' => [
				'computed' => [
					'prop' => fn () => 'computed prop'
				]
			]
		];

		$component = new Component('test');

		$this->assertSame('computed prop', $component->prop());
		$this->assertSame('computed prop', $component->prop);
	}

	/**
	 * @covers ::__construct
	 * @covers ::__call
	 * @covers ::applyComputed
	 */
	public function testComputedFromProp()
	{
		Component::$types = [
			'test' => [
				'props' => [
					'prop' => fn ($prop) => $prop
				],
				'computed' => [
					'prop' => fn () => 'computed: ' . $this->prop
				]
			]
		];

		$component = new Component('test', ['prop' => 'prop value']);

		$this->assertSame('computed: prop value', $component->prop());
	}

	/**
	 * @covers ::__construct
	 * @covers ::__call
	 */
	public function testMethod()
	{
		Component::$types = [
			'test' => [
				'methods' => [
					'say' => fn () => 'hello world'
				]
			]
		];

		$component = new Component('test');

		$this->assertSame('hello world', $component->say());
	}

	/**
	 * @covers ::__construct
	 * @covers ::__call
	 */
	public function testPropsInMethods()
	{
		Component::$types = [
			'test' => [
				'props' => [
					'message' => fn ($message) => $message
				],
				'methods' => [
					'say' => fn () => $this->message
				]
			]
		];

		$component = new Component('test', ['message' => 'hello world']);

		$this->assertSame('hello world', $component->say());
	}

	/**
	 * @covers ::__construct
	 * @covers ::__call
	 */
	public function testComputedPropsInMethods()
	{
		Component::$types = [
			'test' => [
				'props' => [
					'message' => fn ($message) => $message
				],
				'computed' => [
					'message' => fn () => strtoupper($this->message)
				],
				'methods' => [
					'say' => fn () => $this->message
				]
			]
		];

		$component = new Component('test', ['message' => 'hello world']);

		$this->assertSame('HELLO WORLD', $component->say());
	}

	/**
	 * @covers ::toArray
	 * @covers ::__debugInfo
	 */
	public function testToArray()
	{
		Component::$types = [
			'test' => [
				'props' => [
					'message' => fn ($message) => $message
				],
				'computed' => [
					'message' => fn () => strtoupper($this->message)
				],
				'methods' => [
					'say' => fn () => $this->message
				]
			]
		];

		$component = new Component('test', ['message' => 'hello world']);
		$expected  = ['message' => 'HELLO WORLD'];

		$this->assertSame($expected, $component->toArray());
		$this->assertSame($expected, $component->__debugInfo());
	}

	/**
	 * @covers ::toArray
	 * @covers ::__debugInfo
	 */
	public function testCustomToArray()
	{
		Component::$types = [
			'test' => [
				'toArray' => fn () => [
					'foo' => 'bar'
				]
			]
		];

		$component = new Component('test');

		$this->assertSame(['foo' => 'bar'], $component->toArray());
	}

	/**
	 * @covers ::__construct
	 */
	public function testInvalidType()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Undefined component type: test');

		new Component('test');
	}

	/**
	 * @covers ::load
	 */
	public function testLoadInvalidFile()
	{
		Component::$types = ['foo' => 'bar'];
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Component definition bar does not exist');

		Component::load('foo');
	}

	/**
	 * @covers ::__construct
	 * @covers ::setup
	 */
	public function testMixins()
	{
		Component::$mixins = [
			'test' => [
				'computed' => [
					'message' => fn () => strtoupper($this->message)
				]
			]
		];

		Component::$types = [
			'test' => [
				'mixins' => ['test'],
				'props' => [
					'message' => fn ($message) => $message
				]
			]
		];

		$component = new Component('test', ['message' => 'hello world']);

		$this->assertSame('HELLO WORLD', $component->message());
		$this->assertSame('HELLO WORLD', $component->message);
	}

	/**
	 * @covers ::__get
	 */
	public function testGetInvalidProp()
	{
		Component::$types = [
			'test' => []
		];

		$component = new Component('test');
		$this->assertNull($component->foo);
	}

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		Component::$types = [
			'test' => []
		];

		$component = new Component('test');
		$this->assertSame([], $component->defaults());
	}
}
