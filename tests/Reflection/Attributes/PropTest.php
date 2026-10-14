<?php

namespace Kirby\Reflection\Attributes;

use Attribute;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use ReflectionProperty;

class PropTestClass
{
	#[Prop(computed: true)]
	protected array|string|null $label = null;

	#[Prop(default: ['1/1'])]
	protected array|null $layouts = null;

	#[Prop]
	protected string $width = '1/1';
}

#[CoversClass(Prop::class)]
class PropTest extends TestCase
{
	public function testConstruct(): void
	{
		$attribute = new Prop();

		$this->assertFalse($attribute->computed);
		$this->assertNull($attribute->default);
		$this->assertTrue($attribute->export);
		$this->assertNull($attribute->exportAs);
		$this->assertNull($attribute->getter);
	}

	public function testConstructWithComputed(): void
	{
		$attribute = new Prop(computed: true);

		$this->assertTrue($attribute->computed);
	}

	public function testConstructWithDefault(): void
	{
		$attribute = new Prop(default: ['1/1']);

		$this->assertSame(['1/1'], $attribute->default);
	}

	public function testConstructWithExport(): void
	{
		$attribute = new Prop(export: false);

		$this->assertFalse($attribute->export);
	}

	public function testConstructWithExportAs(): void
	{
		$attribute = new Prop(exportAs: 'markerColor');

		$this->assertSame('markerColor', $attribute->exportAs);
	}

	public function testConstructWithGetter(): void
	{
		$attribute = new Prop(getter: 'isDisabled');

		$this->assertSame('isDisabled', $attribute->getter);
	}

	public function testOnProperty(): void
	{
		$property  = new ReflectionProperty(PropTestClass::class, 'width');
		$attribute = $property->getAttributes(Prop::class)[0]->newInstance();

		$this->assertFalse($attribute->computed);
		$this->assertNull($attribute->default);
	}

	public function testOnPropertyWithComputed(): void
	{
		$property  = new ReflectionProperty(PropTestClass::class, 'label');
		$attribute = $property->getAttributes(Prop::class)[0]->newInstance();

		$this->assertTrue($attribute->computed);
	}

	public function testOnPropertyWithDefault(): void
	{
		$property  = new ReflectionProperty(PropTestClass::class, 'layouts');
		$attribute = $property->getAttributes(Prop::class)[0]->newInstance();

		$this->assertSame(['1/1'], $attribute->default);
	}

	public function testTarget(): void
	{
		$class     = new ReflectionClass(Prop::class);
		$attribute = $class->getAttributes(Attribute::class)[0];

		$this->assertSame(
			Attribute::TARGET_PROPERTY,
			$attribute->newInstance()->flags
		);
	}
}
