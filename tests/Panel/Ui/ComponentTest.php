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
	 * @covers ::__call
	 */
	public function testGetterSetter()
	{
		$component = new UiComponent(component: 'k-foo');

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
		$this->expectExceptionMessage('The property "foo" does not exist on the UI component "k-foo"');
		$component = new UiComponent(component: 'k-foo');
		$component->foo('my-class');
	}


	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$component = new UiComponent(
			component: 'k-foo',
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
			component: 'k-foo',
			class: 'my-class'
		);

		$result = $component->render();
		$this->assertSame('k-foo', $result['component']);
		$this->assertIsString($result['key']);
		$this->assertSame(['class' => 'my-class'], $result['props']);
	}
}
