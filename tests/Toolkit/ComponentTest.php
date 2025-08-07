<?php

namespace Kirby\Toolkit;

use ArgumentCountError;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

class TestComponentWithCustomProperty extends Component
{
	public string $b = 'custom property';
}

#[CoversClass(Component::class)]
class ComponentTest extends TestCase
{
	public function tearDown(): void
	{
		Component::$types  = [];
		Component::$mixins = [];
	}

	public function testProp(): void
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

	public function testPropWithDefaultValue(): void
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

	public function testPropWithFixedValue(): void
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

	public function testPropWithInvalidValue(): void
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

	public function testPropWithMissingValue(): void
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

	public function testPropUnsetting(): void
	{
		Component::$types = [
			'a' => [
				'props' => [
					'a' => fn ($prop) => $prop,
					'b' => fn ($prop) => $prop
				]
			],
			'b' => [
				'extends' => 'a',
				'props' => [
					'b' => null
				]
			]
		];

		$component = new Component('b', [
			'a' => 'a',
			'b' => 'b'
		]);

		$this->assertSame('a', $component->a());
		$this->assertNull($component->b());
	}

	public function testPropUnsettingWithCustomProperty(): void
	{
		TestComponentWithCustomProperty::$types = [
			'a' => [
				'props' => [
					'a' => fn ($prop) => $prop,
					'b' => null // this should not have any effect on the custom property
				]
			]
		];

		$component = new TestComponentWithCustomProperty('a', [
			'a' => 'a'
		]);

		$this->assertSame('a', $component->a());
		$this->assertSame('custom property', $component->b());
	}

	public function testAttrs(): void
	{
		Component::$types = [
			'test' => []
		];

		$component = new Component('test', ['foo' => 'bar']);

		$this->assertSame('bar', $component->foo());
		$this->assertSame('bar', $component->foo);
	}

	public function testComputed(): void
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

	public function testComputedFromProp(): void
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

	public function testMethod(): void
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

	public function testPropsInMethods(): void
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

	public function testComputedPropsInMethods(): void
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

	public function testToArray(): void
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

	public function testCustomToArray(): void
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

	public function testInvalidType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Undefined component type: test');

		new Component('test');
	}

	public function testLoadInvalidFile(): void
	{
		Component::$types = ['foo' => 'bar'];
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Component definition bar does not exist');

		Component::load('foo');
	}

	public function testMixins(): void
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

	public function testGetInvalidProp(): void
	{
		Component::$types = [
			'test' => []
		];

		$component = new Component('test');
		$this->assertNull($component->foo);
	}

	public function testDefaults(): void
	{
		Component::$types = [
			'test' => []
		];

		$component = new Component('test');
		$this->assertSame([], $component->defaults());
	}
}
