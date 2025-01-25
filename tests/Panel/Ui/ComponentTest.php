<?php

namespace Kirby\Panel\Ui;

use Exception;
use Kirby\TestCase;

class UiComponent extends Component
{
}

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Component
 * @covers ::__construct
 */
class ComponentTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testAttrs()
	{
		$props = [
			'component' => 'k-text',
			'class'     => 'k-test',
			'foo'       => 'bar'
		];

		$component = new UiComponent(...$props);

		$this->assertSame([
			'class' => 'k-test',
			'style' => null,
			'foo'   => 'bar',
		], $component->props());
	}

	/**
	 * @covers ::__call
	 */
	public function testGetterSetter()
	{
		$component = new UiComponent(component: 'k-test');

		$this->assertNull($component->class);
		$this->assertNull($component->class());
		$component->class('my-class');
		$this->assertSame('my-class', $component->class);
		$this->assertSame('my-class', $component->class());
	}

	/**
	 * @covers ::__call
	 */
	public function testGetterSetterInvalid()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The property "foo" does not exist on the UI component "k-test"');
		$component = new UiComponent(component: 'k-test');
		$component->foo('my-class');
	}

	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$component = new UiComponent(component: 'k-test');

		$this->assertIsString($component->key());
		$this->assertSame($component->render()['key'], $component->key());
	}

	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$component = new UiComponent(
			component: 'k-test',
			class: 'my-class'
		);

		$this->assertSame([
			'class' => 'my-class',
			'style' => null
		], $component->props());
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$component = new UiComponent(
			component: 'k-test',
			class: 'my-class'
		);

		$result = $component->render();
		$this->assertSame('k-test', $result['component']);
		$this->assertIsString($result['key']);
		$this->assertSame(['class' => 'my-class'], $result['props']);
	}
}
