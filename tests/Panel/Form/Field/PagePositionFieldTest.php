<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PagePositionField::class)]
class PagePositionFieldTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'a', 'num' => 1],
					['slug' => 'b', 'num' => 2],
					['slug' => 'c', 'num' => 3]
				]
			]
		]);
	}

	public function testProps(): void
	{
		$field = new PagePositionField(
			page: $this->app->page('b')
		);

		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'icon'        => null,
			'label'       => 'Please select a position',
			'name'        => 'position',
			'options'     => [
				[
					'value' => 1,
					'text'  => 1
				],
				[
					'value'    => 'a',
					'text'     => 'a',
					'disabled' => true
				],
				[
					'value' => 2,
					'text'  => 2
				],
				[
					'value'    => 'c',
					'text'     => 'c',
					'disabled' => true
				],
				[
					'value' => 3,
					'text'  => 3
				],
			],
			'placeholder' => '—',
			'required'    => false,
			'saveable'    => true,
			'translate'   => true,
			'type'        => 'select',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testLabel(): void
	{
		$field = new PagePositionField(
			page: $this->app->page('b'),
			label: 'Test'
		);

		$this->assertSame('Test', $field->label());
	}
}
