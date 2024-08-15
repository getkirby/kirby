<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Panel\Areas\AreaTestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\ViewButton
 * @covers ::__construct
 */
class ViewButtonTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryFromClosure()
	{
		$button = ViewButton::factory(
			fn (string $name) => ['component' => 'k-' . $name . '-view-button'],
			'test',
			['name' => 'foo']
		);

		$this->assertInstanceOf(ViewButton::class, $button);
		$this->assertSame('k-foo-view-button', $button->component);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryFromDefinition()
	{
		$button = ViewButton::factory(
			['component' => 'k-test-view-button'],
			'test'
		);

		$this->assertInstanceOf(ViewButton::class, $button);
		$this->assertSame('k-test-view-button', $button->component);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryFromStringName()
	{
		$app = $this->app->clone([
			'areas' => [
				'test' => fn () => [
					'buttons' => [
						'test' => ['component' => 'result'],
						'foo'  => function () {}
					]
				]
			]
		]);

		// simulate a logged in user
		$app->impersonate('test@getkirby.com');

		$button = ViewButton::factory('test');
		$this->assertInstanceOf(ViewButton::class, $button);
		$this->assertSame('result', $button->component);

		// null returned
		$button = ViewButton::factory('foo');
		$this->assertNull($button);
	}

	/**
	 * @covers ::find
	 */
	public function testFind(): void
	{
		$app = $this->app->clone([
			'areas' => [
				'test' => fn () => [
					'buttons' => [
						'test.a' => ['component' => 'result-a'],
						'b'      => ['component' => 'result-b']
					]
				]
			]
		]);

		// simulate a logged in user
		$app->impersonate('test@getkirby.com');

		// view-prefixed name
		$result = ViewButton::find('a', 'test');
		$this->assertSame(['component' => 'result-a'], $result);

		// generic name
		$result = ViewButton::find('b');
		$this->assertSame(['component' => 'result-b'], $result);

		// custom component
		$result = ViewButton::find('foo');
		$this->assertSame(['component' => 'k-foo-view-button'], $result);
	}

	/**
	 * @covers ::normalize
	 */
	public function testNormalize(): void
	{
		$result = ViewButton::normalize([
			'icon' => 'add'
		]);

		$this->assertSame(['icon' => 'add'], $result);

		// flatten array
		$result = ViewButton::normalize([
			'component' => 'k-foo-view-button',
			'props'     => [
				'icon' => 'add'
			]
		]);

		$this->assertSame([
			'icon'      => 'add',
			'component' => 'k-foo-view-button',
		], $result);
	}

	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$component = new ViewButton(
			icon: 'smile',
			size: 'xs',
			options: '/my/route',
			text: 'Congrats',
			theme: 'positive',
			variant: 'filled'
		);

		$this->assertSame([
			'class'      => null,
			'style'      => null,
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
			'options'    => '/my/route'
		], $component->props());
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$test   = $this;
		$result = ViewButton::resolve(function (string $b, bool $a, App $kirby) use ($test) {
			$test->assertFalse($a);
			$test->assertSame('foo', $b);
			$test->assertInstanceOf(App::class, $kirby);
			return ['component' => 'k-test-view-button'];
		}, [
			'a' => false,
			'b' => 'foo'
		]);

		$this->assertSame('k-test-view-button', $result['component']);

		$result = ViewButton::resolve(['component' => 'k-test-view-button']);
		$this->assertSame('k-test-view-button', $result['component']);
	}
}
