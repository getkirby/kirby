<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilePositionField::class)]
class FilePositionFieldTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'a.jpg'],
					['filename' => 'b.jpg'],
					['filename' => 'c.jpg']
				]
			]
		]);
	}

	public function testProps(): void
	{
		$file = $this->app->site()->file('b.jpg');

		$field = new FilePositionField(
			file: $file
		);

		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'icon'        => null,
			'label'       => 'Change position',
			'name'        => 'position',
			'options'     => [
				[
					'value' => 1,
					'text'  => 1
				],
				[
					'value'    => 'a.jpg',
					'text'     => 'a.jpg',
					'disabled' => true
				],
				[
					'value' => 2,
					'text'  => 2
				],
				[
					'value'    => 'c.jpg',
					'text'     => 'c.jpg',
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
		$field = new FilePositionField(
			file: $this->app->file('b.jpg'),
			label: 'Test'
		);

		$this->assertSame('Test', $field->label());
	}
}
