<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\TestCase;

class BlueprintFieldsTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'fields/test' => [
					'label' => 'Test',
					'type'  => 'text'
				]
			]
		]);
	}

	public function testEmptyFields(): void
	{
		$fields = Blueprint::fieldsProps(false);
		$this->assertSame([], $fields);
	}

	public function testNameOnlyField(): void
	{
		$fields = Blueprint::fieldsProps([
			'text' => true
		]);

		$expected = [
			'text' => [
				'label' => 'Text',
				'name'  => 'text',
				'type'  => 'text',
				'width' => '1/1'
			]
		];

		$this->assertEquals($expected, $fields); // cannot use strict assertion (array order)
	}

	public function testFieldFromString(): void
	{
		$fields = Blueprint::fieldsProps([
			'hello' => 'fields/test'
		]);

		$expected = [
			'hello' => [
				'label' => 'Test',
				'name'  => 'hello',
				'type'  => 'text',
				'width' => '1/1'
			]
		];

		$this->assertEquals($expected, $fields); // cannot use strict assertion (array order)
	}

	public function testFieldGroup(): void
	{
		$fields = Blueprint::fieldsProps([
			'header' => [
				'type'   => 'group',
				'fields' => [
					'headline' => [
						'type' => 'text'
					],
					'intro' => [
						'type' => 'textarea'
					]
				]
			],
			'text' => [
				'type' => 'textarea'
			]
		]);

		$expected = [
			'headline' => [
				'label' => 'Headline',
				'name'  => 'headline',
				'type'  => 'text',
				'width' => '1/1'
			],
			'intro' => [
				'label' => 'Intro',
				'name'  => 'intro',
				'type'  => 'textarea',
				'width' => '1/1'
			],
			'text' => [
				'label' => 'Text',
				'name'  => 'text',
				'type'  => 'textarea',
				'width' => '1/1'
			]
		];

		$this->assertEquals($expected, $fields); // cannot use strict assertion (array order)
	}

	public function testMultipleFieldGroups(): void
	{
		$fields = Blueprint::fieldsProps([
			'header' => [
				'type'   => 'group',
				'fields' => [
					'headline' => [
						'type' => 'text'
					],
					'intro' => [
						'type' => 'textarea'
					]
				]
			],
			'body' => [
				'type'   => 'group',
				'fields' => [
					'tags' => [
						'type' => 'tags'
					],
					'text' => [
						'type' => 'textarea'
					]
				]
			]
		]);

		$expected = [
			'headline' => [
				'label' => 'Headline',
				'name'  => 'headline',
				'type'  => 'text',
				'width' => '1/1'
			],
			'intro' => [
				'label' => 'Intro',
				'name'  => 'intro',
				'type'  => 'textarea',
				'width' => '1/1'
			],
			'tags' => [
				'label' => 'Tags',
				'name'  => 'tags',
				'type'  => 'tags',
				'width' => '1/1'
			],
			'text' => [
				'label' => 'Text',
				'name'  => 'text',
				'type'  => 'textarea',
				'width' => '1/1'
			]
		];

		$this->assertEquals($expected, $fields); // cannot use strict assertion (array order)
	}

	public function testFieldError(): void
	{
		$props = Blueprint::fieldsProps([
			'test' => [
				'type' => 'invalid'
			]
		]);

		$expected = [
			'test' => [
				'label' => 'Error',
				'name'  => 'test',
				'text'  => 'Invalid field type ("invalid")',
				'theme' => 'negative',
				'type'  => 'info'
			]
		];

		$this->assertSame($expected, $props);
	}
}
