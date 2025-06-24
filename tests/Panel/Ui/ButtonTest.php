<?php

namespace Kirby\Panel\Ui;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Button
 * @covers ::__construct
 */
class ButtonTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testAttrs()
	{
		$button = new Button(
			text: 'Attrs',
			foo: 'bar'
		);

		$this->assertSame([
			'foo'        => 'bar',
			'responsive' => true,
			'text'       => 'Attrs',
			'type'       => 'button',
		], array_filter($button->props()));
	}

	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$component = new Button(
			icon: 'smile',
			size: 'xs',
			text: 'Congrats',
			theme: 'positive',
			variant: 'filled'
		);

		$this->assertSame([
			'class'      => null,
			'style'      => null,
			'badge'      => null,
			'current'    => null,
			'dialog'     => null,
			'disabled'   => false,
			'drawer'     => null,
			'dropdown'   => null,
			'icon'       => 'smile',
			'link'       => null,
			'responsive' => true,
			'size'       => 'xs',
			'target'     => null,
			'text'       => 'Congrats',
			'theme'      => 'positive',
			'title'      => null,
			'type'       => 'button',
			'variant'    => 'filled',
		], $component->props());
	}

	/**
	 * @covers ::props
	 */
	public function testPropsWithI18n()
	{
		$component = new Button(
			text: [
				'en' => 'Congrats',
				'de' => 'Glückwunsch'
			],
		);

		$props = $component->props();
		$this->assertSame('Congrats', $props['text']);
	}
}
