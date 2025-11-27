<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkField::class)]
class LinkFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('link');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'default'     => null,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'label'       => 'Link',
			'name'        => 'link',
			'options'     => ['url', 'page', 'file', 'email', 'tel', 'anchor'],
			'required'    => false,
			'saveable'    => true,
			'translate'   => true,
			'type'        => 'link',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testOptionsInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid options: foo, bar');

		$field = $this->field('link', [
			'options' => ['page', 'foo', 'bar']
		]);
		$field->options();
	}
}
