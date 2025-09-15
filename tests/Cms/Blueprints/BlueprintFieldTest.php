<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

class BlueprintFieldTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'fields/test' => [
					'name'  => 'test',
					'label' => 'Test',
					'type'  => 'text'
				]
			]
		]);
	}

	public function testFieldPropsDefaults(): void
	{
		$props = Blueprint::fieldProps([
			'name' => 'test',
			'type' => 'text'
		]);

		$this->assertSame('test', $props['name']);
		$this->assertSame('text', $props['type']);
		$this->assertSame('Test', $props['label']);
		$this->assertSame('1/1', $props['width']);
	}

	public function testFieldTypeFromName(): void
	{
		$props = Blueprint::fieldProps([
			'name' => 'text',
		]);

		$this->assertSame('text', $props['name']);
		$this->assertSame('text', $props['type']);
		$this->assertSame('Text', $props['label']);
	}

	public function testMissingFieldName(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The field name is missing');

		$props = Blueprint::fieldProps([]);
	}

	public function testInvalidFieldType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid field type ("test")');

		$props = Blueprint::fieldProps([
			'name' => 'test',
			'type' => 'test'
		]);
	}

	public function testFieldError(): void
	{
		$props = Blueprint::fieldError('test', 'something went wrong');
		$expected = [
			'label' => 'Error',
			'name'  => 'test',
			'text'  => 'something went wrong',
			'theme' => 'negative',
			'type'  => 'info'
		];

		$this->assertSame($expected, $props);
	}

	public function testExtendField(): void
	{
		$props = Blueprint::fieldProps([
			'name'    => 'test',
			'extends' => 'fields/test'
		]);

		$expected = [
			'label' => 'Test',
			'name'  => 'test',
			'type'  => 'text',
			'width' => '1/1'
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}

	public function testExtendFieldFromString(): void
	{
		$props = Blueprint::fieldProps('fields/test');

		$this->assertSame('test', $props['name']);
		$this->assertSame('Test', $props['label']);
		$this->assertSame('text', $props['type']);
	}

	public function testExtendFieldWithNonAssociativeOptions(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'fields/another-test' => [
					'name'  => 'test',
					'label' => 'Test',
					'type'  => 'textarea',
					'buttons' => [
						'bold',
						'italic'
					]
				]
			]
		]);


		$props = Blueprint::fieldProps([
			'extends' => 'fields/another-test',
			'buttons' => [
				'li'
			]
		]);

		$expected = [
			'buttons' => [
				'li'
			],
			'label' => 'Test',
			'name'  => 'test',
			'type'  => 'textarea',
			'width' => '1/1'
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}

	public function testNestedFields(): void
	{
		$props = Blueprint::fieldProps([
			'name'   => 'test',
			'type'   => 'structure',
			'fields' => [
				'headline' => [
					'type' => 'text'
				]
			]
		]);

		$this->assertSame('headline', $props['fields']['headline']['name']);
		$this->assertSame('Headline', $props['fields']['headline']['label']);
		$this->assertSame('text', $props['fields']['headline']['type']);
		$this->assertSame('1/1', $props['fields']['headline']['width']);
	}

	public function testFieldGroup(): void
	{
		$props = Blueprint::fieldProps([
			'name'   => 'test',
			'type'   => 'group',
			'fields' => [
				'headline' => [
					'type' => 'text'
				]
			]
		]);

		$expected = [
			'fields' => [
				'headline' => [
					'label' => 'Headline',
					'name'  => 'headline',
					'type'  => 'text',
					'width' => '1/1'
				]
			],
			'name' => 'test',
			'type' => 'group'
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}

	public function testFieldGroupWhen(): void
	{
		$props = Blueprint::fieldProps([
			'name'   => 'test',
			'type'   => 'group',
			'when'	 => [
				'category' => 'value'
			],
			'fields' => [
				'headline' => [
					'type' => 'text'
				]
			]
		]);

		$expected = [
			'fields' => [
				'headline' => [
					'label' => 'Headline',
					'name'  => 'headline',
					'type'  => 'text',
					'width' => '1/1',
					'when'	 => [
						'category' => 'value'
					],
				]
			],
			'name' => 'test',
			'type' => 'group'
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}

	public function testFieldGroupWhenMerge(): void
	{
		$props = Blueprint::fieldProps([
			'name'   => 'test',
			'type'   => 'group',
			'when'	 => [
				'category' => 'value',
				'another'  => 'value',
			],
			'fields' => [
				'headline' => [
					'type' => 'text',
					'when' => [
						'category'  => 'different-value',
						'different' => 'field'
					]
				]
			]
		]);

		$expected = [
			'fields' => [
				'headline' => [
					'label' => 'Headline',
					'name'  => 'headline',
					'type'  => 'text',
					'width' => '1/1',
					'when'	 => [
						'category'  => 'different-value',
						'another'   => 'value',
						'different' => 'field'
					],
				]
			],
			'name' => 'test',
			'type' => 'group'
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}
}
