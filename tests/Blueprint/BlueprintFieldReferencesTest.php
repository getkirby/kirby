<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Blueprint::class)]
#[CoversClass(Normalizer::class)]
class BlueprintFieldReferencesTest extends TestCase
{
	protected Page $model;

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->model = new Page(['slug' => 'test']);
	}

	public function testBackwardsCompatibility(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'title' => [
					'type' => 'text'
				],
				'date' => [
					'type' => 'date'
				]
			]
		]);

		// Fields should be available in the global registry
		$this->assertArrayHasKey('title', $blueprint->fields());
		$this->assertArrayHasKey('date', $blueprint->fields());
		$this->assertSame('text', $blueprint->fields()['title']['type']);
		$this->assertSame('date', $blueprint->fields()['date']['type']);

		// Fields should be placed in the main tab's column
		$tabs = $blueprint->toArray()['tabs'];
		$this->assertArrayHasKey('main', $tabs);
		$fields = $tabs['main']['columns'][0]['fields'];
		$this->assertArrayHasKey('title', $fields);
		$this->assertArrayHasKey('date', $fields);
	}

	public function testFieldReferenceWithExtends(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'fields/customtext' => [
					'type'  => 'text',
					'label' => 'Custom Text Field'
				]
			]
		]);

		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'myField' => [
					'extends' => 'fields/customtext'
				]
			],
			'tabs' => [
				'content' => [
					'fields' => [
						'myField'
					]
				]
			]
		]);

		// Field should be resolved with extended properties
		$tabs = $blueprint->toArray()['tabs'];
		$sectionFields = $tabs['content']['columns'][0]['fields'];
		$this->assertArrayHasKey('myField', $sectionFields);
		$this->assertSame('text', $sectionFields['myField']['type']);
		$this->assertSame('Custom Text Field', $sectionFields['myField']['label']);
	}

	public function testGlobalFieldsInColumns(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'text' => [
					'type' => 'textarea'
				],
				'date' => [
					'type' => 'date'
				]
			],
			'columns' => [
				[
					'width' => '2/3',
					'fields' => [
						'text'
					]
				],
				[
					'width' => '1/3',
					'fields' => [
						'date'
					]
				]
			]
		]);

		$tabs = $blueprint->toArray()['tabs'];
		$columns = $tabs['main']['columns'];

		// First column should have the text field
		$this->assertArrayHasKey('text', $columns[0]['fields']);
		$this->assertSame('textarea', $columns[0]['fields']['text']['type']);

		// Second column should have the date field
		$this->assertArrayHasKey('date', $columns[1]['fields']);
		$this->assertSame('date', $columns[1]['fields']['date']['type']);
	}

	public function testGlobalFieldsInMultipleSections(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'title' => [
					'type' => 'text'
				],
				'date' => [
					'type' => 'date'
				],
				'tags' => [
					'type' => 'tags'
				]
			],
			'tabs' => [
				'main' => [
					'sections' => [
						'a' => [
							'type'   => 'fields',
							'fields' => ['title']
						],
						'b' => [
							'type'   => 'fields',
							'fields' => ['date', 'tags']
						]
					]
				]
			]
		]);

		// references keep their own name instead of colliding
		// on the numeric key they are written with
		$fields = $blueprint->fields();
		$this->assertSame(['title', 'date', 'tags'], array_keys($fields));
		$this->assertSame('text', $fields['title']['type']);
		$this->assertSame('date', $fields['date']['type']);
		$this->assertSame('tags', $fields['tags']['type']);

		$tabs = $blueprint->toArray()['tabs'];
		$this->assertSame(
			['title', 'date', 'tags'],
			array_keys($tabs['main']['columns'][0]['fields'])
		);
	}

	public function testGlobalFieldsInSectionAndLevelFields(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'title' => [
					'type' => 'text'
				],
				'date' => [
					'type' => 'date'
				]
			],
			'tabs' => [
				'main' => [
					'fields'   => ['title'],
					'sections' => [
						's' => [
							'type'   => 'fields',
							'fields' => ['date']
						]
					]
				]
			]
		]);

		$fields = $blueprint->fields();
		$this->assertSame(['title', 'date'], array_keys($fields));
		$this->assertSame('text', $fields['title']['type']);
		$this->assertSame('date', $fields['date']['type']);
	}

	public function testGlobalFieldsInSections(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'myField' => [
					'type' => 'text'
				]
			],
			'sections' => [
				'content' => [
					'type'   => 'fields',
					'fields' => [
						'myField'
					]
				]
			]
		]);

		// Field should be resolved in the section
		$tabs = $blueprint->toArray()['tabs'];
		$sectionFields = $tabs['main']['columns'][0]['fields'];
		$this->assertArrayHasKey('myField', $sectionFields);
		$this->assertSame('text', $sectionFields['myField']['type']);
	}

	public function testGlobalFieldsInTabs(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'date' => [
					'type' => 'date'
				],
				'author' => [
					'type' => 'users'
				],
				'text' => [
					'type' => 'textarea'
				]
			],
			'tabs' => [
				'content' => [
					'fields' => [
						'text'
					]
				],
				'meta' => [
					'fields' => [
						'date',
						'author'
					]
				]
			]
		]);

		// All fields should be in the global registry
		$fields = $blueprint->fields();
		$this->assertArrayHasKey('date', $fields);
		$this->assertArrayHasKey('author', $fields);
		$this->assertArrayHasKey('text', $fields);

		// Fields should be resolved in tabs
		$tabs = $blueprint->toArray()['tabs'];

		// Content tab should have the text field
		$contentFields = $tabs['content']['columns'][0]['fields'];
		$this->assertArrayHasKey('text', $contentFields);
		$this->assertSame('textarea', $contentFields['text']['type']);

		// Meta tab should have date and author fields
		$metaFields = $tabs['meta']['columns'][0]['fields'];
		$this->assertArrayHasKey('date', $metaFields);
		$this->assertArrayHasKey('author', $metaFields);
		$this->assertSame('date', $metaFields['date']['type']);
		$this->assertSame('users', $metaFields['author']['type']);
	}

	public function testInlineFieldsExtractedToGlobal(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'tabs'  => [
				'content' => [
					'fields' => [
						'inlineField' => [
							'type' => 'text'
						]
					]
				]
			]
		]);

		// Inline field should be available in the global registry
		$fields = $blueprint->fields();
		$this->assertArrayHasKey('inlineField', $fields);
		$this->assertSame('text', $fields['inlineField']['type']);
	}

	public function testInternalAttributesAreRemoved(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'myField' => [
					'type' => 'text'
				]
			],
			'tabs' => [
				'content' => [
					'fields' => [
						'myField'
					]
				]
			]
		]);

		// Field should be present without any internal attributes
		$fields = $blueprint->fields();
		$this->assertArrayHasKey('myField', $fields);
		$this->assertSame('text', $fields['myField']['type']);

		// Field should be accessible via field()
		$field = $blueprint->field('myField');
		$this->assertSame('text', $field['type']);

		// Field should be present in section
		$tabs = $blueprint->toArray()['tabs'];
		$sectionFields = $tabs['content']['columns'][0]['fields'];
		$this->assertSame('text', $sectionFields['myField']['type']);
	}

	public function testMixedGlobalAndInlineFields(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'globalField' => [
					'type' => 'date'
				]
			],
			'tabs' => [
				'content' => [
					'fields' => [
						'globalField',
						'inlineField' => [
							'type' => 'textarea'
						]
					]
				]
			]
		]);

		// Both fields should be in the global registry
		$fields = $blueprint->fields();
		$this->assertArrayHasKey('globalField', $fields);
		$this->assertArrayHasKey('inlineField', $fields);
		$this->assertSame('date', $fields['globalField']['type']);
		$this->assertSame('textarea', $fields['inlineField']['type']);

		// Both fields should be in the tab
		$tabs = $blueprint->toArray()['tabs'];
		$sectionFields = $tabs['content']['columns'][0]['fields'];
		$this->assertArrayHasKey('globalField', $sectionFields);
		$this->assertArrayHasKey('inlineField', $sectionFields);
	}

	public function testSameFieldReferencedMultipleTimes(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'sharedField' => [
					'type' => 'text',
					'label' => 'Shared'
				]
			],
			'tabs' => [
				'tab1' => [
					'fields' => [
						'sharedField'
					]
				],
				'tab2' => [
					'fields' => [
						'sharedField'
					]
				]
			]
		]);

		$tabs = $blueprint->toArray()['tabs'];

		// First tab should have the field
		$tab1Fields = $tabs['tab1']['columns'][0]['fields'];
		$this->assertArrayHasKey('sharedField', $tab1Fields);
		$this->assertSame('text', $tab1Fields['sharedField']['type']);
		$this->assertSame('Shared', $tab1Fields['sharedField']['label']);

		// Second tab should show an error because field is already used
		$tab2Fields = $tabs['tab2']['columns'][0]['fields'];
		$this->assertArrayHasKey('sharedField', $tab2Fields);
		$this->assertSame('info', $tab2Fields['sharedField']['type']);
		$this->assertSame('negative', $tab2Fields['sharedField']['theme']);
		$this->assertStringContainsString('already exists', $tab2Fields['sharedField']['text']);
	}

	public function testSectionWhenPushedToReferencedFields(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'title' => [
					'type' => 'text'
				]
			],
			'sections' => [
				'meta' => [
					'type' => 'fields',
					'when' => ['category' => 'a'],
					'fields' => [
						'title',
						'inline' => ['type' => 'text']
					]
				]
			]
		]);

		// the section condition is pushed down onto referenced
		// fields just like onto inline ones
		$fields = $blueprint->fields();
		$this->assertSame(['category' => 'a'], $fields['title']['when']);
		$this->assertSame(['category' => 'a'], $fields['inline']['when']);
	}

	public function testUndefinedFieldReference(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'existingField' => [
					'type' => 'text'
				]
			],
			'tabs' => [
				'content' => [
					'fields' => [
						'nonExistentField'
					]
				]
			]
		]);

		// The non-existent field should be converted to an error field
		$tabs = $blueprint->toArray()['tabs'];
		$sectionFields = $tabs['content']['columns'][0]['fields'];

		$this->assertArrayHasKey('nonExistentField', $sectionFields);
		$this->assertSame('info', $sectionFields['nonExistentField']['type']);
		$this->assertSame('negative', $sectionFields['nonExistentField']['theme']);
		$this->assertStringContainsString('nonExistentField', $sectionFields['nonExistentField']['text']);
	}

}
