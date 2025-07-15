<?php

namespace Kirby\Panel\Ui;

use Exception;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class UiComponent extends Component
{
	public function __construct(
		public string $component,
		public string|null $class = null,
		public string|null $style = null,
		public array $foo = [],
		...$attrs
	) {
		$this->attrs = $attrs;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'foo' => $this->foo
		];
	}
}

#[CoversClass(Component::class)]
class ComponentTest extends TestCase
{
	public function testAttrs(): void
	{
		$props = [
			'component' => 'k-text',
			'class'     => 'k-test',
			'fox'       => 'bar'
		];

		$component = new UiComponent(...$props);

		$this->assertSame([
			'class' => 'k-test',
			'style' => null,
			'fox'   => 'bar',
			'foo'   => []
		], $component->props());
	}

	public function testGetterSetter(): void
	{
		$component = new UiComponent(component: 'k-test');

		$this->assertNull($component->class);
		$this->assertNull($component->class());
		$component->class('my-class');
		$this->assertSame('my-class', $component->class);
		$this->assertSame('my-class', $component->class());
	}

	public function testGetterSetterInvalid(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The property "fox" does not exist on the UI component "k-test"');
		$component = new UiComponent(component: 'k-test');
		$component->fox('my-class');
	}

	public function testKey(): void
	{
		$component = new UiComponent(component: 'k-test');

		$this->assertIsString($component->key());
		$this->assertSame($component->render()['key'], $component->key());
	}

	public function testProps(): void
	{
		$component = new UiComponent(
			component: 'k-test',
			class: 'my-class'
		);

		$this->assertSame([
			'class' => 'my-class',
			'style' => null,
			'foo'   => []
		], $component->props());
	}

	public function testRender(): void
	{
		$component = new UiComponent(
			component: 'k-test',
			class: 'my-class',
			foo: ['a']
		);

		$result = $component->render();
		$this->assertSame('k-test', $result['component']);
		$this->assertIsString($result['key']);
		$this->assertSame(['class' => 'my-class', 'foo' => ['a']], $result['props']);
	}

	public function testRenderDontFilterEmptyArrayProp(): void
	{
		$component = new UiComponent(
			component: 'k-test',
		);

		$result = $component->render();
		$this->assertSame(['foo' => []], $result['props']);
	}
}
