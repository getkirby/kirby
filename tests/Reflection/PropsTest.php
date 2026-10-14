<?php

namespace Kirby\Reflection;

use Kirby\Exception\LogicException;
use Kirby\Reflection\Attributes\Prop;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

class PropsTestClass
{
	protected array|null $cache = ['foo'];

	/**
	 * Only used in the backend
	 */
	#[Prop(export: false)]
	protected string $hidden = 'hidden';

	/**
	 * The label of the field
	 */
	#[Prop]
	protected string|null $label = 'label';

	/**
	 * The color of the marker
	 */
	#[Prop(exportAs: 'markerColor')]
	protected string $marker = 'red';

	#[Prop]
	protected string $uninitialized;

	/**
	 * The width of the field in the field grid
	 *
	 * @since 1.0.0
	 */
	#[Prop]
	protected string $width = '1/1';

	#[Prop]
	protected string $withArgs = 'raw';

	#[Prop]
	protected string $withPrivateGetter = 'raw';

	public function label(): string
	{
		return 'Label via getter';
	}

	public function withArgs(string $suffix): string
	{
		return $this->withArgs . $suffix;
	}

	private function withPrivateGetter(): string
	{
		return 'private';
	}
}

class PropsTestChildClass extends PropsTestClass
{
	/**
	 * The fieldsets that the getter resolves into a collection
	 */
	#[Prop(computed: true, default: ['heading', 'text'])]
	protected array|null $fieldsets = null;

	/**
	 * An optional icon
	 */
	#[Prop(computed: true)]
	protected string|null $icon = null;

	#[Prop]
	protected string $width = '1/2';
}

class PropsTestRedeclaredClass extends PropsTestClass
{
	// redeclared without attribute to change the defaults
	protected string $hidden = 'redeclared';
	protected string $width = '1/3';
}

class PropsTestSetterClass
{
	#[Prop(getter: 'isActive')]
	protected bool $active = false;

	#[Prop]
	protected array $settings = ['size' => 'small', 'colors' => ['red', 'blue']];

	#[Prop]
	protected array $step = ['size' => 1, 'unit' => 'day'];

	#[Prop]
	protected array $tags = ['a', 'b', 'c'];

	#[Prop]
	protected bool $toggle = false;

	public function option(string $name): mixed
	{
		return $this->$name;
	}

	protected function isActive(): bool
	{
		return $this->active === false;
	}

	protected function setStep(array|int|string $step): void
	{
		$this->step = match (true) {
			is_int($step)    => [...$this->step, 'size' => $step],
			is_string($step) => [...$this->step, 'unit' => $step],
			default          => [...$this->step, ...$step]
		};
	}
}

class PropsTestCollisionClass
{
	#[Prop(exportAs: 'b')]
	protected string $a = 'a';

	#[Prop]
	protected string $b = 'b';
}

class PropsTestInvalidGetterClass
{
	#[Prop(getter: 'missing')]
	protected string $a = 'a';
}

#[CoversClass(Props::class)]
class PropsTest extends TestCase
{
	public function testAll(): void
	{
		$props = (new Props(PropsTestClass::class))->all();

		$this->assertSame(
			['hidden', 'label', 'marker', 'uninitialized', 'width', 'withArgs', 'withPrivateGetter'],
			array_keys($props)
		);

		$this->assertInstanceOf(Prop::class, $props['label']['attribute']);
		$this->assertSame('label', $props['label']['getter']->getName());
		$this->assertSame('label', $props['label']['property']->getName());

		// only public getters without required arguments are used
		$this->assertNull($props['width']['getter']);
		$this->assertNull($props['withArgs']['getter']);
		$this->assertNull($props['withPrivateGetter']['getter']);
	}

	public function testAllWithExportCollision(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The props "a" and "b" of Kirby\Reflection\PropsTestCollisionClass are both exported as "b"');

		(new Props(PropsTestCollisionClass::class))->all();
	}

	public function testAllWithInheritedProperties(): void
	{
		$props = (new Props(new PropsTestChildClass()))->all();

		$this->assertSame(
			['fieldsets', 'hidden', 'icon', 'label', 'marker', 'uninitialized', 'width', 'withArgs', 'withPrivateGetter'],
			array_keys($props)
		);
	}

	public function testAllWithInvalidGetter(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The getter "missing" for the prop "a" must be a non-static method');

		(new Props(PropsTestInvalidGetterClass::class))->all();
	}

	public function testAllWithRedeclaredProperty(): void
	{
		$props = new Props(new PropsTestRedeclaredClass());

		// the attribute of the parent declaration is used
		$this->assertTrue($props->has('width'));
		$this->assertTrue($props->has('hidden'));
		$this->assertFalse($props->all()['hidden']['attribute']->export);

		$values = $props->values();
		$this->assertSame('1/3', $values['width']);
		$this->assertArrayNotHasKey('hidden', $values);
	}

	public function testHas(): void
	{
		$props = new Props(PropsTestClass::class);

		$this->assertTrue($props->has('label'));
		$this->assertTrue($props->has('hidden'));
		$this->assertFalse($props->has('cache'));
		$this->assertFalse($props->has('foo'));
	}

	public function testSet(): void
	{
		$object = new PropsTestSetterClass();
		$props  = new Props($object);

		// associative arrays are merged, lists are replaced
		$props->set('settings', ['colors' => ['green']]);
		$props->set('tags', ['b']);
		$props->set('toggle', true);

		$this->assertSame(['size' => 'small', 'colors' => ['green']], $object->option('settings'));
		$this->assertSame(['b'], $object->option('tags'));
		$this->assertTrue($object->option('toggle'));
	}

	public function testSetWithClassName(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Props can only be set or exported for an object');

		(new Props(PropsTestSetterClass::class))->set('toggle', true);
	}

	public function testSetWithInvalidType(): void
	{
		$this->expectException(TypeError::class);

		(new Props(new PropsTestSetterClass()))->set('toggle', ['yes']);
	}

	public function testSetWithSetter(): void
	{
		$object = new PropsTestSetterClass();
		$props  = new Props($object);

		$props->set('step', 5);
		$this->assertSame(['size' => 5, 'unit' => 'day'], $object->option('step'));

		$props->set('step', 'week');
		$this->assertSame(['size' => 5, 'unit' => 'week'], $object->option('step'));
	}

	public function testSetWithUnknownProp(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The prop "foo" does not exist');

		(new Props(new PropsTestSetterClass()))->set('foo', 'bar');
	}

	public function testToArray(): void
	{
		$props = (new Props(PropsTestClass::class))->toArray();

		$this->assertSame([
			'name'        => 'width',
			'type'        => 'string',
			'computed'    => false,
			'default'     => '1/1',
			'description' => 'The width of the field in the field grid'
		], $props['width']);

		// props that are not exported are still documented
		$this->assertSame('Only used in the backend', $props['hidden']['description']);
	}

	public function testToArrayWithComputedProp(): void
	{
		$props = (new Props(PropsTestChildClass::class))->toArray();

		$this->assertTrue($props['icon']['computed']);
		$this->assertNull($props['icon']['default']);
		$this->assertSame('An optional icon', $props['icon']['description']);
	}

	public function testToArrayWithExportAs(): void
	{
		$props = (new Props(PropsTestClass::class))->toArray();

		// the documentation uses the original prop name
		$this->assertSame('marker', $props['marker']['name']);
	}

	public function testToArrayWithOverriddenDefault(): void
	{
		$props = (new Props(PropsTestChildClass::class))->toArray();

		// the child only redeclares the property to change the default,
		// so the description still comes from the parent
		$this->assertSame('1/2', $props['width']['default']);
		$this->assertSame('The width of the field in the field grid', $props['width']['description']);
	}

	public function testToArrayWithPropDefault(): void
	{
		$props = (new Props(PropsTestChildClass::class))->toArray();

		// the attribute can document a default the property never holds
		$this->assertTrue($props['fieldsets']['computed']);
		$this->assertSame(['heading', 'text'], $props['fieldsets']['default']);
	}

	public function testToArrayWithoutDefaultValue(): void
	{
		$props = (new Props(PropsTestClass::class))->toArray();

		$this->assertNull($props['uninitialized']['default']);
		$this->assertNull($props['uninitialized']['description']);
	}

	public function testType(): void
	{
		$props = new Props(PropsTestSetterClass::class);

		$this->assertSame('bool', $props->type('toggle'));

		// the setter can accept more types than the property
		$this->assertSame('array|string|int', $props->type('step'));

		$this->assertNull($props->type('foo'));
	}

	public function testValues(): void
	{
		$props = new Props(new PropsTestClass());

		$this->assertSame([
			'label'             => 'Label via getter',
			'markerColor'       => 'red',
			'uninitialized'     => null,
			'width'             => '1/1',
			'withArgs'          => 'raw',
			'withPrivateGetter' => 'raw'
		], $props->values());
	}

	public function testValuesWithClassName(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Props can only be set or exported for an object');

		(new Props(PropsTestClass::class))->values();
	}

	public function testValuesWithGetterAttribute(): void
	{
		$values = (new Props(new PropsTestSetterClass()))->values();

		// the protected getter from the attribute is used
		$this->assertTrue($values['active']);
	}
}
