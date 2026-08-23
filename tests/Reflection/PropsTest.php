<?php

namespace Kirby\Reflection;

use Exception;
use Kirby\Reflection\Attributes\Computed;
use Kirby\TestCase;
use Kirby\Toolkit\Collection;
use PHPUnit\Framework\Attributes\CoversClass;

class PropsTestClass
{
	/**
	 * The width of the field in the field grid
	 *
	 * @since 1.0.0
	 */
	protected string|null $width;

	/**
	 * The value that gets stored
	 */
	protected mixed $value;

	public function __construct(
		mixed $value = null,
		string|null $width = null
	) {
		$this->value = $value;
		$this->width = $width;
	}

	public function width(): string
	{
		return $this->width ?? '1/1';
	}
}

class PropsTestChildClass extends PropsTestClass
{
	/**
	 * The types the getter never returns as such
	 */
	#[Computed(default: ['heading', 'text'])]
	protected array|null $fieldsets;

	/**
	 * An optional icon
	 */
	#[Computed]
	protected string|null $icon;

	/**
	 * Needs a model to resolve
	 */
	protected string|null $report;

	/**
	 * Resolved into a collection
	 */
	protected array|null $tags;

	public function __construct(
		array|null $fieldsets = null,
		string|null $icon = null,
		string|null $report = 'fallback',
		array|null $tags = null,
		array|null $value = null,
		mixed ...$args
	) {
		parent::__construct(...$args, value: $value);

		$this->fieldsets = $fieldsets;
		$this->icon      = $icon;
		$this->report    = $report;
		$this->tags      = $tags;
	}

	public function fieldsets(): array
	{
		return $this->fieldsets ?? ['computed', 'from', 'somewhere'];
	}

	public function icon(): string
	{
		return $this->icon ?? 'derived-from-somewhere';
	}

	public function report(): string
	{
		throw new Exception('The model is missing');
	}

	public function tags(): Collection
	{
		return new Collection($this->tags ?? []);
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
			'description' => 'The width of the field in the field grid'
		], $props['width']);
	}

	public function testToArrayWithComputedAttribute(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// the attribute wins over the computed getter value
		$this->assertNull($props['icon']['default']);
		$this->assertSame('An optional icon', $props['icon']['description']);
	}

	public function testToArrayWithComputedAttributeDefault(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// the attribute can document a default the getter never returns
		$this->assertSame(['heading', 'text'], $props['fieldsets']['default']);
	}

	public function testToArrayWithInheritedParameters(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// resolved through the variadic parameter, sorted by name
		$this->assertSame(
			['fieldsets', 'icon', 'report', 'tags', 'value', 'width'],
			array_keys($props)
		);
		$this->assertSame('1/1', $props['width']['default']);
		$this->assertSame('The width of the field in the field grid', $props['width']['description']);
	}

	public function testToArrayWithObjectGetter(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// a collection is a view of the prop, never its default
		$this->assertNull($props['tags']['default']);
		$this->assertSame('Resolved into a collection', $props['tags']['description']);
	}

	public function testToArrayWithRefinedParameter(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// the child narrows `mixed` and must only show up once
		$this->assertSame('?array', $props['value']['type']);

		// the description still comes from the parent's property
		$this->assertSame('The value that gets stored', $props['value']['description']);
	}

	public function testToArrayWithThrowingGetter(): void
	{
		$props = (new Props(new PropsTestChildClass()))->toArray();

		// falls back to the parameter default instead of bubbling up
		$this->assertSame('fallback', $props['report']['default']);
	}

	public function testToArrayWithoutDefaultValue(): void
	{
		$props = (new Props(new PropsTestRequiredClass('test')))->toArray();

		$this->assertNull($props['title']['default']);
		$this->assertNull($props['title']['description']);
	}

	public function testToArrayWithoutGetter(): void
	{
		$props = (new Props(new PropsTestClass()))->toArray();

		$this->assertNull($props['value']['default']);
		$this->assertSame('The value that gets stored', $props['value']['description']);
	}

	public function testToArrayWithoutProperty(): void
	{
		$props = (new Props(new PropsTestPropertylessClass()))->toArray();

		// a parameter that isn't stored has no docblock to describe it
		$this->assertSame([
			'name'        => 'type',
			'type'        => '?string',
			'default'     => null,
			'description' => null
		], $props['type']);
	}
}
