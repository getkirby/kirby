<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

class SectionTest extends TestCase
{
	protected array $sectionTypes;

	public function setUp(): void
	{
		App::destroy();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->sectionTypes = Section::$types;
	}

	public function tearDown(): void
	{
		Section::$types = $this->sectionTypes;
	}

	public function testApi(): void
	{
		// no defined as default
		Section::$types = [
			'test' => []
		];

		$model = new Page(['slug' => 'test']);

		$section = new Section('test', [
			'model' => $model,
		]);

		$this->assertNull($section->api());

		// return simple string
		Section::$types = [
			'test' => [
				'api' => fn () => 'Hello World'
			]
		];

		$model = new Page(['slug' => 'test']);

		$section = new Section('test', [
			'model' => $model,
		]);

		$this->assertSame('Hello World', $section->api());
	}

	public function testDialogs(): void
	{
		// no defined as default
		Section::$types = [
			'test' => []
		];

		$model = new Page(['slug' => 'test']);

		$section = new Section('test', [
			'model' => $model,
		]);

		$this->assertSame([], $section->dialogs());

		// test dialogs
		$routes = [
			[
				'pattern' => 'foo',
				'load'    => function () {},
				'submit'  => function () {}
			]
		];

		// return routes
		Section::$types = [
			'test' => [
				'dialogs' => fn () => $routes
			]
		];

		$section = new Section('test', [
			'model' => $model,
		]);

		$this->assertSame($routes, $section->dialogs());
	}

	public function testDialogsInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Dialogs of section "test" must be defined as a closure');

		Section::$types = [
			'test' => [
				'dialogs' => 'foo'
			]
		];

		$model = new Page(['slug' => 'test']);
		$section = new Section('test', ['model' => $model]);
		$section->dialogs();
	}

	public function testDrawers(): void
	{
		// no defined as default
		Section::$types = [
			'test' => []
		];

		$model = new Page(['slug' => 'test']);
		$section = new Section('test', [
			'model' => $model,
		]);

		$this->assertSame([], $section->drawers());

		// test drawers
		$routes = [
			[
				'pattern' => 'foo',
				'load'    => function () {},
				'submit'  => function () {}
			]
		];

		// return routes
		Section::$types = [
			'test' => [
				'drawers' => fn () => $routes
			]
		];

		$section = new Section('test', [
			'model' => $model,
		]);

		$this->assertSame($routes, $section->drawers());
	}

	public function testDrawersInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Drawers of section "test" must be defined as a closure');

		Section::$types = [
			'test' => [
				'drawers' => 'foo'
			]
		];

		$model = new Page(['slug' => 'test']);
		$section = new Section('test', ['model' => $model]);
		$section->drawers();
	}

	public function testMissingModel(): void
	{
		Section::$types['test'] = [];

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Undefined section model');

		$section = new Section('test', []);
	}

	public function testPropsDefaults(): void
	{
		Section::$types['test'] = [
			'props' => [
				'example' => fn ($example = 'default') => $example,
				'buttons' => fn ($buttons = ['one', 'two']) => $buttons
			]
		];

		$section = new Section('test', [
			'model' => new Page(['slug' => 'test'])
		]);

		$this->assertSame('default', $section->example());
		$this->assertSame(['one', 'two'], $section->buttons());
	}

	public function testToResponse(): void
	{
		Section::$types['test'] = [
			'props' => [
				'a' => fn ($a) => $a,
				'b' => fn ($b) => $b
			]
		];

		$section = new Section('test', [
			'model' => new Page(['slug' => 'test']),
			'a' => 'A',
			'b' => 'B'
		]);


		$expected = [
			'status' => 'ok',
			'code'   => 200,
			'name'   => 'test',
			'type'   => 'test',
			'a'      => 'A',
			'b'      => 'B'
		];

		$this->assertSame($expected, $section->toResponse());
	}
}
