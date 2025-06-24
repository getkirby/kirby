<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Page;
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
	 * @covers ::__construct
	 */
	public function testAttrs()
	{
		$button = new ViewButton(
			text: 'Attrs',
			foo: 'bar'
		);

		$this->assertSame([
			'foo'        => 'bar',
			'responsive' => true,
			'size'       => 'sm',
			'text'       => 'Attrs',
			'type'       => 'button',
			'variant'    => 'filled'
		], array_filter($button->props()));
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryFromClosure()
	{
		$button = ViewButton::factory(
			button: fn (string $name) => [
				'component' => 'k-' . $name . '-view-button'
			],
			view: 'test',
			data: ['name' => 'foo']
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
			button: ['component' => 'k-test-view-button'],
			view: 'test'
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

		$button = ViewButton::factory(name: 'test');
		$this->assertInstanceOf(ViewButton::class, $button);
		$this->assertSame('result', $button->component);

		$button = ViewButton::factory(button: 'test');
		$this->assertInstanceOf(ViewButton::class, $button);
		$this->assertSame('result', $button->component);

		// null returned
		$button = ViewButton::factory('foo');
		$this->assertNull($button);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryWithNameArg()
	{
		$button = ViewButton::factory(
			button: [],
			name: 'foo',
			view: 'test'
		);

		$this->assertInstanceOf(ViewButton::class, $button);
		$this->assertSame('k-foo-view-button', $button->component);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryFromBooleanButton()
	{
		// Default
		$button = ViewButton::factory(
			button: true,
			name: 'foo',
			view: 'test'
		);
		$this->assertInstanceOf(ViewButton::class, $button);
		$this->assertSame('k-foo-view-button', $button->component);

		// Disable the button
		$button = ViewButton::factory(
			button: false,
			name: 'foo',
			view: 'test'
		);
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
		$result = ViewButton::find('a', view: 'test');
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
			'options'    => '/my/route'
		], $component->props());
	}

	/**
	 * @covers ::props
	 */
	public function testPropsWithQueries()
	{
		$model     = new Page(['slug' => 'test']);
		$component = new ViewButton(
			model: $model,
			text: 'Page: {{ page.url }}',
			link: 'https://getkirby.com/{{ page.slug }}',
		);

		$props = $component->props();
		$this->assertSame('Page: /test', $props['text']);
		$this->assertSame('https://getkirby.com/test', $props['link']);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$test   = $this;
		$model  = new Page(['slug' => 'test']);
		$result = ViewButton::resolve(
			button: function (string $b, bool $a, App $kirby, Page $page) use ($test) {
				$test->assertFalse($a);
				$test->assertSame('foo', $b);
				$test->assertInstanceOf(App::class, $kirby);
				$test->assertInstanceOf(Page::class, $page);
				return ['component' => 'k-test-view-button'];
			},
			model: $model,
			data: [
				'a' => false,
				'b' => 'foo'
			]
		);

		$this->assertSame('k-test-view-button', $result['component']);
	}
}
