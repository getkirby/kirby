<?php

namespace Kirby\Reflection\Attributes;

use Attribute;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use ReflectionProperty;

class ComputedTestClass
{
	#[Computed]
	protected array|string|null $label = null;

	#[Computed(default: ['1/1'])]
	protected array|null $layouts = null;
}

#[CoversClass(Computed::class)]
class ComputedTest extends TestCase
{
	public function testConstruct(): void
	{
		$attribute = new Computed();

		$this->assertNull($attribute->default);
	}

	public function testConstructWithDefault(): void
	{
		$attribute = new Computed(default: ['1/1']);

		$this->assertSame(['1/1'], $attribute->default);
	}

	public function testOnProperty(): void
	{
		$property  = new ReflectionProperty(ComputedTestClass::class, 'label');
		$attribute = $property->getAttributes(Computed::class)[0];

		$this->assertNull($attribute->newInstance()->default);
	}

	public function testOnPropertyWithDefault(): void
	{
		$property  = new ReflectionProperty(ComputedTestClass::class, 'layouts');
		$attribute = $property->getAttributes(Computed::class)[0];

		$this->assertSame(['1/1'], $attribute->newInstance()->default);
	}

	public function testTarget(): void
	{
		$class     = new ReflectionClass(Computed::class);
		$attribute = $class->getAttributes(Attribute::class)[0];

		// `Kirby\Reflection\Props` only reads the attribute from properties
		$this->assertSame(
			Attribute::TARGET_PROPERTY,
			$attribute->newInstance()->flags
		);
	}
}
