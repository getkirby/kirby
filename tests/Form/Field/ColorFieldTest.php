<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ColorField::class)]
class ColorFieldTest extends TestCase
{
	public function testDefault(): void
	{
		$field = $this->field('color', [
			'default' => '#fff',
		]);

		$this->assertSame('#fff', $field->default());
	}

	public function testFormat(): void
	{
		$field = $this->field('color', ['format' => 'rgb']);
		$this->assertSame('rgb', $field->format());

		$field = $this->field('color');
		$this->assertSame('hex', $field->format());

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid format "foo" in color field');

		$field = $this->field('color', ['format' => 'foo']);
		$field->format();
	}

	public function testIsColor(): void
	{
		$this->assertTrue(ColorField::isColor('#f00'));
		$this->assertTrue(ColorField::isColor('#ff0000'));
		$this->assertTrue(ColorField::isColor('#ff0000aa'));
		$this->assertTrue(ColorField::isColor('rgb(255,0,0)'));
		$this->assertTrue(ColorField::isColor('rgba(255, 0, 0, 0.6)'));
		$this->assertTrue(ColorField::isColor('hsl(0, 100%, 50%)'));
		$this->assertTrue(ColorField::isColor('hsla(0, 100%, 50%, .6)'));

		$this->assertFalse(ColorField::isColor('#47'));
		$this->assertFalse(ColorField::isColor('rgp(255, 0, 0,.6)'));
	}

	public function testIsHex(): void
	{
		$this->assertTrue(ColorField::isHex('#f00'));
		$this->assertTrue(ColorField::isHex('#ff0000'));
		$this->assertTrue(ColorField::isHex('#ff0000aa'));

		$this->assertFalse(ColorField::isHex('#47'));
	}

	public function testIsHsl(): void
	{
		$this->assertTrue(ColorField::isHsl('hsl(0, 100%, 50%)'));
		$this->assertTrue(ColorField::isHsl('hsla(0, 100%, 50%, .6)'));

		$this->assertFalse(ColorField::isColor('hzl(0, 100%, 50%)'));
	}

	public function testIsRgb(): void
	{
		$this->assertTrue(ColorField::isRgb('rgb(255,0,0)'));
		$this->assertTrue(ColorField::isRgb('rgba(255, 0, 0, 0.6)'));

		$this->assertFalse(ColorField::isRgb('rgp(255, 0, 0,.6)'));
	}

	public function testIsValid(): void
	{
		$field = $this->field('color');
		$this->assertTrue($field->isValid());

		$field->fill('#ddd');
		$this->assertTrue($field->isValid());

		$field->fill('#ödd');
		$this->assertFalse($field->isValid());
	}

	public function testMode(): void
	{
		$field = $this->field('color', ['mode' => 'options']);
		$this->assertSame('options', $field->mode());

		$field = $this->field('color');
		$this->assertSame('picker', $field->mode());

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid mode "foo" in color field');

		$field = $this->field('color', ['mode' => 'foo']);
		$field->mode();
	}

	public function testOptions(): void
	{
		// Only values
		$field = $this->field('color', [
			'options' => ['#aaa', '#bbb', '#ccc'],
		]);

		$this->assertSame([
			['value' => '#aaa'],
			['value' => '#bbb'],
			['value' => '#ccc']
		], $field->options());

		// Value => Name
		$field = $this->field('color', [
			'options' => [
				'#aaa' => 'Color a',
				'#bbb' => 'Color b',
				'#ccc' => 'Color c'
			],
		]);

		$this->assertSame([
			['value' => '#aaa', 'text' => 'Color a'],
			['value' => '#bbb', 'text' => 'Color b'],
			['value' => '#ccc', 'text' => 'Color c']
		], $field->options());
	}

	public function testOptionsFromQuery(): void
	{
		// Only values
		$this->app = new App([
			'options' => [
				'foo' => ['#aaa', '#bbb', '#ccc']
			]
		]);

		$field = $this->field('color', [
			'options' => ['type' => 'query', 'query' => 'kirby.option("foo")'],
		]);

		$this->assertSame([
			['value' => '#aaa'],
			['value' => '#bbb'],
			['value' => '#ccc']
		], $field->options());

		// Value => Name
		$this->app = new App([
			'options' => [
				'foo' => [
					'#aaa' => 'Color a',
					'#bbb' => 'Color b',
					'#ccc' => 'Color c'
				]
			]
		]);

		$field = $this->field('color', [
			'options' => ['type' => 'query', 'query' => 'kirby.option("foo")'],
		]);

		$this->assertSame([
			['value' => '#aaa', 'text' => 'Color a'],
			['value' => '#bbb', 'text' => 'Color b'],
			['value' => '#ccc', 'text' => 'Color c']
		], $field->options());
	}

	public function testProps(): void
	{
		$field = $this->field('color');
		$props = $field->props();

		ksort($props);

		$expected = [
			'alpha'       => false,
			'autofocus'   => false,
			'disabled'    => false,
			'format'      => 'hex',
			'help'        => null,
			'hidden'      => false,
			'icon'        => null,
			'label'       => 'Color',
			'mode'        => 'picker',
			'name'        => 'color',
			'options'     => [],
			'placeholder' => null,
			'required'    => false,
			'saveable'    => true,
			'translate'   => true,
			'type'        => 'color',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testReset(): void
	{
		$field = $this->field('color');
		$field->fill('#efefef');
		$this->assertSame('#efefef', $field->toFormValue());

		$field->reset();
		$this->assertSame('', $field->toFormValue());
	}

	public function testValueEmpty(): void
	{
		$field = $this->field('color', [
			'value' => null
		]);

		$this->assertSame('', $field->toFormValue());
		$this->assertSame('', $field->toStoredValue());
	}
}
