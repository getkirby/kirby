<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Normalizer::class)]
class NormalizerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Blueprint.Normalizer';

	protected function setUp(): void
	{
		parent::setUp();

		$this->setUpSingleLanguage([
			'children' => [
				['slug' => 'a']
			]
		]);

		$this->setUpTmp();
	}

	protected function tearDown(): void
	{
		App::destroy();

		$this->tearDownTmp();

		parent::tearDown();
	}

	protected function normalizer(array $props = []): Normalizer
	{
		return new Normalizer(props: $props);
	}

	public function testColumnsWithEmptyFields(): void
	{
		$normalizer = $this->normalizer([
			'columns' => [
				['width' => '1/3'],
				['width' => '2/3']
			]
		]);

		$columns = $normalizer->tabs()['main']['columns'];

		$this->assertSame([
			'label' => 'Column (1/3)',
			'type'  => 'info',
			'text'  => 'No fields yet',
			'name'  => 'main-info-0',
			'width' => '1/1'
		], $columns[0]['fields']['main-info-0']);

		$this->assertSame([
			'label' => 'Column (2/3)',
			'type'  => 'info',
			'text'  => 'No fields yet',
			'name'  => 'main-info-1',
			'width' => '1/1'
		], $columns[1]['fields']['main-info-1']);
	}

	public function testColumnsWithFields(): void
	{
		$normalizer = $this->normalizer([
			'columns' => [
				[
					'width'  => '1/2',
					'fields' => [
						'title' => ['type' => 'text']
					]
				]
			]
		]);

		$fields = $normalizer->tabs()['main']['columns'][0]['fields'];

		$this->assertArrayHasKey('title', $fields);
		$this->assertSame('text', $fields['title']['type']);
	}

	public function testColumnsWithInvalidColumn(): void
	{
		$normalizer = $this->normalizer([
			'columns' => [
				'a' => 'nonsense',
				'b' => ['width' => '1/1']
			]
		]);

		$columns = $normalizer->tabs()['main']['columns'];

		$this->assertSame(['b'], array_keys($columns));
	}

	public function testColumnsWithoutWidth(): void
	{
		$normalizer = $this->normalizer([
			'columns' => [
				['fields' => ['info' => ['type' => 'info']]]
			]
		]);

		$this->assertSame(
			'1/1',
			$normalizer->tabs()['main']['columns'][0]['width']
		);
	}

	public function testConvertColumnsToTabs(): void
	{
		$normalizer = $this->normalizer([
			'columns' => [
				['width' => '1/1']
			]
		]);

		$this->assertArrayNotHasKey('columns', $normalizer->props());
		$this->assertSame(['main'], array_keys($normalizer->tabs()));
	}

	public function testConvertFieldsToColumns(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'title' => ['type' => 'text']
			]
		]);

		$this->assertArrayNotHasKey('fields', $normalizer->props());

		$columns = $normalizer->tabs()['main']['columns'];

		$this->assertCount(1, $columns);
		$this->assertSame('1/1', $columns[0]['width']);
		$this->assertSame(['title'], array_keys($columns[0]['fields']));
	}

	public function testConvertSectionsToFields(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'pages' => ['type' => 'pages']
			]
		]);

		$this->assertArrayNotHasKey('sections', $normalizer->props());

		$columns = $normalizer->tabs()['main']['columns'];

		$this->assertCount(1, $columns);
		$this->assertSame('1/1', $columns[0]['width']);
		$this->assertSame(['pages'], array_keys($columns[0]['fields']));
	}

	public function testFieldReference(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'title' => ['type' => 'text']
			],
			'sections' => [
				'content' => [
					'type'   => 'fields',
					'fields' => ['title']
				]
			]
		]);

		$fields = $normalizer->fields()->toArray();

		$this->assertSame(['title'], array_keys($fields));
		$this->assertSame('text', $fields['title']['type']);
		$this->assertSame('Title', $fields['title']['label']);
	}

	public function testFieldReferenceInGroup(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'title' => ['type' => 'text', 'maxlength' => 5]
			],
			'tabs' => [
				'main' => [
					'fields' => [
						'meta' => ['type' => 'group', 'fields' => ['title']]
					]
				]
			]
		]);

		$field = $normalizer->fields()->get('title');

		$this->assertSame('text', $field['type']);
		$this->assertSame(5, $field['maxlength']);
	}

	public function testFieldReferenceInNestedFields(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'title' => ['type' => 'text', 'maxlength' => 5]
			],
			'tabs' => [
				'main' => [
					'fields' => [
						'items' => ['type' => 'structure', 'fields' => ['title']]
					]
				]
			]
		]);

		$field = $normalizer->fields()->get('items')['fields']['title'];

		$this->assertSame('text', $field['type']);
		$this->assertSame(5, $field['maxlength']);
	}

	public function testFieldReferenceMissing(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'title' => ['type' => 'text']
			],
			'sections' => [
				'content' => [
					'type'   => 'fields',
					'fields' => ['nonsense']
				]
			]
		]);

		$field = $normalizer->fields()->toArray()['nonsense'];

		$this->assertSame('info', $field['type']);
		$this->assertSame('Error', $field['label']);
		$this->assertSame(
			'Referenced field "nonsense" is not defined in fields',
			$field['text']
		);
	}

	public function testFieldReferenceInDifferentCase(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'MixedCase' => ['type' => 'text']
			],
			'sections' => [
				'content' => [
					'type'   => 'fields',
					'fields' => ['mixedcase']
				]
			]
		]);

		// the lookup is case-insensitive and the definition keeps
		// the name it was declared with, so that the automatic
		// label is derived from that spelling
		$fields = $normalizer->fields()->toArray();

		$this->assertSame(['MixedCase'], array_keys($fields));
		$this->assertSame('text', $fields['MixedCase']['type']);
		$this->assertSame('Mixed case', $fields['MixedCase']['label']);
	}

	public function testFieldReferenceTwice(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'title' => ['type' => 'text']
			],
			'sections' => [
				'content' => [
					'type'   => 'fields',
					'fields' => ['title', 'title']
				]
			]
		]);

		// a reference claims the name like any other field, so the
		// same definition cannot be pulled in twice
		$fields = $normalizer->fields()->toArray();

		$this->assertSame(['title', 'title-duplicate-1'], array_keys($fields));
		$this->assertSame('text', $fields['title']['type']);
		$this->assertSame('info', $fields['title-duplicate-1']['type']);
	}

	public function testFields(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'title' => ['type' => 'text'],
				'date'  => ['type' => 'date']
			]
		]);

		$fields = $normalizer->fields()->toArray();

		$this->assertSame(['title', 'date'], array_keys($fields));
		$this->assertSame('Title', $fields['title']['label']);
		$this->assertSame('1/1', $fields['title']['width']);
		$this->assertSame('date', $fields['date']['type']);
	}

	public function testFieldsEmpty(): void
	{
		$this->assertSame([], $this->normalizer()->fields()->toArray());
	}

	public function testFieldsWithDuplicateName(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'a' => [
					'type'   => 'fields',
					'fields' => ['title' => ['type' => 'text']]
				],
				'b' => [
					'type'   => 'fields',
					'fields' => ['title' => ['type' => 'text']]
				]
			]
		]);

		// the first field keeps the name it claimed
		$this->assertSame('text', $normalizer->fields()->toArray()['title']['type']);

		// the duplicate becomes an error field with a name of its own
		$error = $normalizer->fields()->toArray()['title-duplicate-1'];

		$this->assertSame('info', $error['type']);
		$this->assertSame('negative', $error['theme']);
		$this->assertSame(
			'The field <strong>"title"</strong> already exists in your blueprint',
			$error['text']
		);
	}

	public function testFieldsWithDuplicateNameAcrossTabs(): void
	{
		$normalizer = $this->normalizer([
			'tabs' => [
				'images' => [
					'sections' => [
						'files' => ['type' => 'files', 'template' => 'image']
					]
				],
				'docs' => [
					'sections' => [
						'files' => ['type' => 'files', 'template' => 'document']
					]
				]
			]
		]);

		// the first occurrence keeps the registry entry, so everything
		// that reads the blueprint by name still gets a working field
		$field = $normalizer->fields()->toArray()['files'];

		$this->assertSame('filelist', $field['type']);
		$this->assertSame('image', $field['template']);

		// the error field is registered under its own name, so that
		// the Panel can resolve and render it from the registry
		$error = $normalizer->fields()->toArray()['files-duplicate-1'];

		$this->assertSame('info', $error['type']);

		// only the duplicate is rendered as an error field
		$tabs = $normalizer->tabs();

		$this->assertSame(
			['files'],
			array_keys($tabs['images']['columns'][0]['fields'])
		);
		$this->assertSame(
			['files-duplicate-1'],
			array_keys($tabs['docs']['columns'][0]['fields'])
		);
		$this->assertSame(
			'info',
			$tabs['docs']['columns'][0]['fields']['files-duplicate-1']['type']
		);
	}

	public function testFieldsWithDuplicateNameInDifferentCase(): void
	{
		$normalizer = $this->normalizer([
			'tabs' => [
				'one' => [
					'fields' => [
						'Alpha' => [
							'type'      => 'text',
							'label'     => 'First',
							'required'  => true,
							'maxlength' => 3
						]
					]
				],
				'two' => [
					'fields' => [
						'alpha' => ['type' => 'textarea', 'label' => 'Second']
					]
				]
			]
		]);

		// names are lowercased further down the line, so `Alpha` and
		// `alpha` are the same field and collide with each other
		$fields = $normalizer->fields()->toArray();

		$this->assertSame(
			['Alpha', 'alpha-duplicate-1'],
			array_keys($fields)
		);

		// the first field keeps its validation
		$this->assertSame('text', $fields['Alpha']['type']);
		$this->assertTrue($fields['Alpha']['required']);
		$this->assertSame(3, $fields['Alpha']['maxlength']);

		$this->assertSame('info', $fields['alpha-duplicate-1']['type']);
	}

	public function testFieldsWithDuplicateNameInGroup(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'alpha' => ['type' => 'text', 'label' => 'Plain'],
				'group' => [
					'type'   => 'group',
					'fields' => [
						'alpha' => ['type' => 'textarea', 'label' => 'Grouped']
					]
				]
			]
		]);

		// a group is spliced into its siblings, so its members
		// share a single namespace with them
		$fields = $normalizer->fields()->toArray();

		$this->assertSame('text', $fields['alpha']['type']);
		$this->assertSame('Plain', $fields['alpha']['label']);
		$this->assertSame('info', $fields['alpha-duplicate-1']['type']);
	}

	public function testFieldsWithDuplicateNameInGroupFirst(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'group' => [
					'type'   => 'group',
					'fields' => [
						'alpha' => ['type' => 'textarea', 'label' => 'Grouped']
					]
				],
				'alpha' => ['type' => 'text', 'label' => 'Plain']
			]
		]);

		// a group member claims its name like every other field,
		// so the one that comes first keeps it
		$fields = $normalizer->fields()->toArray();

		$this->assertSame('textarea', $fields['alpha']['type']);
		$this->assertSame('Grouped', $fields['alpha']['label']);
		$this->assertSame('info', $fields['alpha-duplicate-1']['type']);
	}

	public function testNormalizeFieldProps(): void
	{
		$props = Normalizer::normalizeFieldProps([
			'name' => 'title',
			'type' => 'text'
		]);

		$this->assertSame('title', $props['name']);
		$this->assertSame('text', $props['type']);
		$this->assertSame('Title', $props['label']);
		$this->assertSame('1/1', $props['width']);
	}

	public function testNormalizeFieldPropsWithGroup(): void
	{
		$props = Normalizer::normalizeFieldProps([
			'name'   => 'meta',
			'type'   => 'group',
			'when'   => ['title' => 'test'],
			'fields' => [
				'date' => ['type' => 'date']
			]
		]);

		$this->assertSame([
			'fields' => [
				'date' => [
					'when'  => ['title' => 'test'],
					'type'  => 'date',
					'name'  => 'date',
					'label' => 'Date',
					'width' => '1/1'
				]
			],
			'name' => 'meta',
			'type' => 'group'
		], $props);
	}

	public function testNormalizeFieldPropsWithInvalidType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid field type ("nonsense")');

		Normalizer::normalizeFieldProps([
			'name' => 'title',
			'type' => 'nonsense'
		]);
	}

	public function testNormalizeFieldPropsWithMissingName(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The field name is missing');

		Normalizer::normalizeFieldProps(['type' => 'text']);
	}

	public function testNormalizeFieldPropsWithNestedFields(): void
	{
		$props = Normalizer::normalizeFieldProps([
			'name'   => 'entries',
			'type'   => 'structure',
			'fields' => [
				'date' => ['type' => 'date']
			]
		]);

		$this->assertSame('Date', $props['fields']['date']['label']);
		$this->assertSame('date', $props['fields']['date']['type']);
	}

	public function testNormalizeFieldPropsWithNestedFieldsInDifferentCase(): void
	{
		$props = Normalizer::normalizeFieldProps([
			'name'   => 'entries',
			'type'   => 'structure',
			'fields' => [
				'Date' => ['type' => 'date'],
				'date' => ['type' => 'text']
			]
		]);

		// nested fields share a namespace of their own, in which
		// `Date` and `date` are the same field
		$this->assertSame(
			['Date', 'date-duplicate-1'],
			array_keys($props['fields'])
		);

		$this->assertSame('date', $props['fields']['Date']['type']);
		$this->assertSame('info', $props['fields']['date-duplicate-1']['type']);
	}

	public function testNormalizeFieldPropsWithTypeFromName(): void
	{
		$props = Normalizer::normalizeFieldProps(['name' => 'text']);

		$this->assertSame('text', $props['type']);
	}

	public function testNormalizeFieldsProps(): void
	{
		$fields = Normalizer::normalizeFieldsProps([
			'title' => ['type' => 'text']
		]);

		$this->assertSame([
			'title' => [
				'type'  => 'text',
				'name'  => 'title',
				'label' => 'Title',
				'width' => '1/1'
			]
		], $fields);
	}

	public function testNormalizeFieldsPropsWithEmptyGroup(): void
	{
		$fields = Normalizer::normalizeFieldsProps([
			'meta' => [
				'type'   => 'group',
				'fields' => []
			]
		]);

		$this->assertSame([], $fields);
	}

	public function testNormalizeFieldsPropsWithFalse(): void
	{
		$fields = Normalizer::normalizeFieldsProps([
			'title' => ['type' => 'text'],
			'date'  => false
		]);

		$this->assertSame(['title'], array_keys($fields));
	}

	public function testNormalizeFieldsPropsWithGroup(): void
	{
		$fields = Normalizer::normalizeFieldsProps([
			'meta' => [
				'type'   => 'group',
				'fields' => [
					'date' => ['type' => 'date']
				]
			],
			'title' => ['type' => 'text']
		]);

		$this->assertSame(['date', 'title'], array_keys($fields));
	}

	public function testNormalizeFieldsPropsWithInvalidName(): void
	{
		$fields = Normalizer::normalizeFieldsProps([
			['name' => ['nonsense'], 'type' => 'text']
		]);

		// a name that cannot be used as such falls back to the key
		$this->assertSame(0, $fields[0]['name']);
		$this->assertSame('text', $fields[0]['type']);
	}

	public function testNormalizeFieldsPropsWithInvalidType(): void
	{
		$fields = Normalizer::normalizeFieldsProps([
			'title' => ['type' => 'nonsense']
		]);

		$this->assertSame([
			'label' => 'Error',
			'name'  => 'title',
			'text'  => 'Invalid field type ("nonsense")',
			'theme' => 'negative',
			'type'  => 'info',
		], $fields['title']);
	}

	public function testNormalizeFieldsPropsWithNonArray(): void
	{
		$this->assertSame([], Normalizer::normalizeFieldsProps(false));
		$this->assertSame([], Normalizer::normalizeFieldsProps(null));
		$this->assertSame([], Normalizer::normalizeFieldsProps('nonsense'));
	}

	public function testNormalizeFieldsPropsWithNull(): void
	{
		$fields = Normalizer::normalizeFieldsProps([
			'text' => null
		]);

		$this->assertSame('text', $fields['text']['type']);
	}

	public function testNormalizeFieldsPropsWithString(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'fields/headline' => [
					'type'  => 'text',
					'label' => 'Headline'
				]
			]
		]);

		$fields = Normalizer::normalizeFieldsProps([
			'title' => 'fields/headline'
		]);

		$this->assertSame('text', $fields['title']['type']);
		$this->assertSame('Headline', $fields['title']['label']);
	}

	public function testNormalizeFieldsPropsWithTrue(): void
	{
		$fields = Normalizer::normalizeFieldsProps([
			'text' => true
		]);

		$this->assertSame('text', $fields['text']['type']);
	}

	public function testNormalizeOptionsWithAliases(): void
	{
		$options = Normalizer::normalizeOptions(
			options: ['create' => false],
			defaults: ['changeTitle' => true, 'create' => true],
			aliases: ['create' => 'changeTitle']
		);

		$this->assertSame([
			'changeTitle' => false,
			'create'      => true
		], $options);
	}

	public function testNormalizeOptionsWithArray(): void
	{
		$options = Normalizer::normalizeOptions(
			options: ['delete' => false],
			defaults: ['delete' => true, 'update' => true]
		);

		$this->assertSame([
			'delete' => false,
			'update' => true
		], $options);
	}

	public function testNormalizeOptionsWithFalse(): void
	{
		$options = Normalizer::normalizeOptions(
			options: false,
			defaults: ['delete' => true, 'update' => true]
		);

		$this->assertSame([
			'delete' => false,
			'update' => false
		], $options);
	}

	public function testNormalizeOptionsWithString(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'options/default' => ['delete' => false]
			]
		]);

		$options = Normalizer::normalizeOptions(
			options: 'options/default',
			defaults: ['delete' => true, 'update' => true]
		);

		$this->assertSame([
			'delete' => false,
			'update' => true
		], $options);
	}

	public function testNormalizeOptionsWithTrue(): void
	{
		$options = Normalizer::normalizeOptions(
			options: true,
			defaults: ['delete' => true, 'update' => false]
		);

		$this->assertSame([
			'delete' => true,
			'update' => false
		], $options);
	}

	public function testProps(): void
	{
		$this->assertSame([
			'name'  => 'default',
			'title' => 'Default',
			'tabs'  => []
		], $this->normalizer()->props());
	}

	public function testPropsWithExtends(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'test' => ['title' => 'Extension Test']
			]
		]);

		$props = $this->normalizer(['extends' => 'test'])->props();

		$this->assertArrayNotHasKey('extends', $props);
		$this->assertSame('Extension Test', $props['title']);
	}

	public function testPropsWithName(): void
	{
		$props = $this->normalizer(['name' => 'article'])->props();

		$this->assertSame('article', $props['name']);
		$this->assertSame('Article', $props['title']);
	}

	public function testPropsWithTitle(): void
	{
		$props = $this->normalizer([
			'name'  => 'article',
			'title' => 'Blog post'
		])->props();

		$this->assertSame('Blog post', $props['title']);
	}

	public function testPropsWithTranslatedTitle(): void
	{
		I18n::$locale       = 'de';
		I18n::$translations = [
			'de' => ['blueprint.title' => 'Artikel']
		];

		$props = $this->normalizer([
			'title' => 'blueprint.title'
		])->props();

		$this->assertSame('Artikel', $props['title']);
	}

	public function testSections(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'pages' => ['type' => 'pages'],
				'files' => ['type' => 'files'],
				'notes' => ['type' => 'pages', 'status' => 'listed']
			]
		]);

		$fields = $normalizer->fields()->toArray();

		$this->assertSame(['pages', 'files', 'notes'], array_keys($fields));

		// section types with a field equivalent are mapped onto that field
		$this->assertSame('pagelist', $fields['pages']['type']);
		$this->assertSame('filelist', $fields['files']['type']);
		$this->assertSame('listed', $fields['notes']['status']);
	}

	public function testSectionsWithFalse(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'pages' => ['type' => 'pages'],
				'files' => false
			]
		]);

		$this->assertSame(['pages'], array_keys($normalizer->fields()->toArray()));
	}

	public function testSectionsWithFieldsType(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'content' => [
					'type'   => 'fields',
					'fields' => ['title' => ['type' => 'text']]
				]
			]
		]);

		// a fields section is unwrapped into the parent's own fields
		$this->assertSame(['title'], array_keys($normalizer->fields()->toArray()));
	}

	public function testSectionsWithInvalidType(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'test' => ['type' => []]
			]
		]);

		$field = $normalizer->fields()->toArray()['test'];

		$this->assertSame('info', $field['type']);
		$this->assertSame(
			'Invalid section type for section "test"',
			$field['label']
		);
	}

	public function testSectionsWithTrue(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'pages' => true
			]
		]);

		$this->assertSame('pagelist', $normalizer->fields()->toArray()['pages']['type']);
	}

	public function testSectionsWithTypeFromName(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'pages' => []
			]
		]);

		$this->assertSame('pagelist', $normalizer->fields()->toArray()['pages']['type']);
	}

	public function testSectionsWithUnknownType(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'test' => ['type' => 'nonsense']
			]
		]);

		$field = $normalizer->fields()->toArray()['test'];

		$this->assertSame('info', $field['type']);
		$this->assertSame('Invalid section type ("nonsense")', $field['label']);
	}

	public function testSectionsWithUnsetField(): void
	{
		$normalizer = $this->normalizer([
			'columns' => [
				[
					'fields'   => ['foo' => false],
					'sections' => ['foo' => ['type' => 'pages']]
				]
			]
		]);

		// the field is unset, so the section can claim its name
		$this->assertSame(['foo'], array_keys($normalizer->fields()->toArray()));
		$this->assertSame('pagelist', $normalizer->fields()->get('foo')['type']);
	}

	public function testSectionsWithoutFields(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'content' => ['type' => 'fields']
			]
		]);

		$this->assertSame([
			'label' => 'Column (1/1)',
			'type'  => 'info',
			'text'  => 'No fields yet',
			'name'  => 'main-info-0',
			'width' => '1/1'
		], $normalizer->fields()->toArray()['main-info-0']);
	}

	public function testTabs(): void
	{
		$normalizer = $this->normalizer([
			'tabs' => [
				'content' => [
					'icon' => 'text'
				]
			]
		]);

		$tab = $normalizer->tabs()['content'];

		$this->assertSame('content', $tab['name']);
		$this->assertSame('Content', $tab['label']);
		$this->assertSame('text', $tab['icon']);
		$this->assertSame([], $tab['columns']);
	}

	public function testTabsEmpty(): void
	{
		$this->assertSame([], $this->normalizer()->tabs());
	}

	public function testTabsWithExtends(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'tabs/seo' => [
					'label' => 'SEO',
					'icon'  => 'search'
				]
			]
		]);

		$normalizer = $this->normalizer([
			'tabs' => [
				'seo' => ['extends' => 'tabs/seo']
			]
		]);

		$tab = $normalizer->tabs()['seo'];

		$this->assertSame('SEO', $tab['label']);
		$this->assertSame('search', $tab['icon']);
	}

	public function testTabsWithFalse(): void
	{
		$normalizer = $this->normalizer([
			'tabs' => [
				'content' => [],
				'seo'     => false
			]
		]);

		$this->assertSame(['content'], array_keys($normalizer->tabs()));
	}

	public function testTabsWithInvalidValue(): void
	{
		$this->assertSame([], $this->normalizer(['tabs' => 'nonsense'])->tabs());
	}

	public function testTabsWithLabel(): void
	{
		$normalizer = $this->normalizer([
			'tabs' => [
				'content' => ['label' => 'My content']
			]
		]);

		$this->assertSame('My content', $normalizer->tabs()['content']['label']);
	}
}
