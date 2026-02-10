<?php

namespace Kirby\Option;

use Kirby\Cms\Page;
use Kirby\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Option::class)]
class OptionTest extends TestCase
{
	public function testConstruct(): void
	{
		// string
		$option = new Option('test');
		$this->assertSame('test', $option->value);
		$this->assertSame('test', $option->id());
		$this->assertSame('test', $option->text['en']);

		// int
		$option = new Option(1);
		$this->assertSame(1, $option->value);
		$this->assertSame(1, $option->id());
		$this->assertSame(1, $option->text['en']);

		// float
		$option = new Option(1.1);
		$this->assertSame(1.1, $option->value);
		$this->assertSame(1.1, $option->id());
		$this->assertSame(1.1, $option->text['en']);
	}

	public function testFactoryWithJustValue(): void
	{
		// string
		$option = Option::factory('test');
		$this->assertSame('test', $option->value);

		// int
		$option = Option::factory(1);
		$this->assertSame(1, $option->value);

		// float
		$option = Option::factory(1.0);
		$this->assertSame(1.0, $option->value);
	}

	public function testFactoryWithValueAndText(): void
	{
		// string
		$option = Option::factory([
			'value' => 'test',
			'text'  => 'Test Option'
		]);

		$this->assertSame('test', $option->value);
		$this->assertSame('Test Option', $option->text['en']);

		// array
		$option = Option::factory([
			'value' => 'test',
			'text'  => [
				'de' => 'Test Option'
			]
		]);

		$this->assertSame('test', $option->value);
		$this->assertSame('Test Option', $option->text['de']);
	}

	public function testRender(): void
	{
		$option = Option::factory([
			'value' => 'test',
			'text'  => 'Test Option',
			'info'  => '{{ page.slug }}'
		]);

		$expected = [
			'disabled' => false,
			'icon'     => null,
			'info'     => 'test',
			'text'     => 'Test Option',
			'value'    => 'test',
		];

		$model = new Page(['slug' => 'test']);
		$this->assertSame($expected, $option->render($model));
	}

	public function testRenderWithoutSafeMode(): void
	{
		$option = Option::factory([
			'value' => 'test',
			'text'  => "{{ page.something.or('String with <> HTML chars') }}",
			'info'  => "{< page.something.or('String with <> HTML chars') >}"
		]);

		$model  = new Page(['slug' => 'test']);
		$result = $option->render($model, safeMode: false);

		$this->assertSame('String with <> HTML chars', $result['text']);
		$this->assertSame('String with <> HTML chars', $result['info']);
	}
}
