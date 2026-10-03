<?php

namespace Kirby\Reflection;

use Exception;
use Kirby\Reflection\Attributes\Derived;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class PropsTestClass
{
	/**
	 * The width of the field in the field grid
	 *
	 * @since 1.0.0
	 */
	protected string $width = '1/1';

	/**
	 * The value that gets stored
	 */
	protected mixed $value;

	public function __construct(
		mixed $value = null,
		string|null $width = null
	) {
		$this->value = $value;
		$this->width = $width ?? $this->width;
	}

	public function width(): string
	{
		throw new Exception('Getters are never called');
	}
}

class PropsTestChildClass extends PropsTestClass
{
	/**
	 * The fieldsets that the getter resolves into a collection
	 */
	#[Derived(default: ['heading', 'text'])]
	protected array|null $fieldsets;

	/**
	 * An optional icon
	 */
	#[Derived]
	protected string|null $icon;

	protected string $width = '1/2';

	public function __construct(
		array|null $fieldsets = null,
		string|null $icon = null,
		array|null $value = null,
		mixed ...$args
	) {
		parent::__construct(...$args, value: $value);

		$this->fieldsets = $fieldsets;
		$this->icon      = $icon;
	}
}

class PropsTestPropertylessClass
{
	public function __construct(
		string|null $type = null
	) {
	}
}

class PropsTestRequiredClass
{
	protected string $title;

	public function __construct(string $title)
	{
		$this->title = $title;
	}
}

#[CoversClass(Props::class)]
class PropsTest extends TestCase
{
	public function testToArray(): void
	{
		$props = (new Props(new PropsTestClass()))->toArray();

		$this->assertSame(['value', 'width'], array_keys($props));
		$this->assertSame([
			'name'        => 'width',
			'type'        => '?string',
			'default'     => '1/1',
			'derived'     => false,
			'description' => 'The width of the field in the field grid'
		], $props['width']);
	}

	public function testToArrayFromClassName(): void
	{
		// the class never has to be instantiated to be described
		$props = (new Props(PropsTestChildClass::class))->toArray();

		$this->assertSame('1/2', $props['width']['default']);
	}

	public function testToArrayWithDerivedAttribute(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// there is no default to document, the value is computed
		$this->assertTrue($props['icon']['derived']);
		$this->assertNull($props['icon']['default']);
		$this->assertSame('An optional icon', $props['icon']['description']);
	}

	public function testToArrayWithDerivedAttributeDefault(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// the attribute can document a default the getter never returns
		$this->assertTrue($props['fieldsets']['derived']);
		$this->assertSame(['heading', 'text'], $props['fieldsets']['default']);
	}

	public function testToArrayWithInheritedParameters(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// resolved through the variadic parameter, sorted by name
		$this->assertSame(
			['fieldsets', 'icon', 'value', 'width'],
			array_keys($props)
		);
	}

	public function testToArrayWithOverriddenDefault(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// the child only redeclares the property to change the default,
		// so the description still comes from the parent
		$this->assertSame('1/2', $props['width']['default']);
		$this->assertSame('The width of the field in the field grid', $props['width']['description']);
	}

	public function testToArrayWithRefinedParameter(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// the child narrows `mixed` and must only show up once
		$this->assertSame('?array', $props['value']['type']);

		// the description still comes from the parent's property
		$this->assertSame('The value that gets stored', $props['value']['description']);
	}

	public function testToArrayWithoutDefaultValue(): void
	{
		$props = (new Props(new PropsTestRequiredClass('test')))->toArray();

		$this->assertNull($props['title']['default']);
		$this->assertNull($props['title']['description']);
	}

	public function testToArrayWithoutProperty(): void
	{
		$props = (new Props(new PropsTestPropertylessClass()))->toArray();

		// a parameter that isn't stored has no docblock to describe it
		$this->assertSame([
			'name'        => 'type',
			'type'        => '?string',
			'default'     => null,
			'derived'     => false,
			'description' => null
		], $props['type']);
	}
}
