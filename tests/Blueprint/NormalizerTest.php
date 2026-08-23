<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Normalizer::class)]
class NormalizerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Blueprint.Normalizer';

	protected ModelWithContent $model;

	protected function setUp(): void
	{
		parent::setUp();

		$this->setUpSingleLanguage([
			'children' => [
				['slug' => 'a']
			]
		]);

		$this->model = $this->app->page('a');
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
		return new Normalizer(
			model: $this->model,
			props: $props
		);
	}

	public function testColumnsWithEmptySections(): void
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
			'text'  => 'No sections yet',
			'name'  => 'main-info-0'
		], $columns[0]['sections']['main-info-0']);

		$this->assertSame([
			'label' => 'Column (2/3)',
			'type'  => 'info',
			'text'  => 'No sections yet',
			'name'  => 'main-info-1'
		], $columns[1]['sections']['main-info-1']);
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

		$sections = $normalizer->tabs()['main']['columns'][0]['sections'];

		$this->assertArrayHasKey('main-col-0-fields', $sections);
		$this->assertSame('fields', $sections['main-col-0-fields']['type']);
		$this->assertArrayHasKey('title', $sections['main-col-0-fields']['fields']);
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
				['sections' => ['info' => ['type' => 'info']]]
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

	public function testConvertFieldsToSections(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'title' => ['type' => 'text']
			]
		]);

		$this->assertArrayNotHasKey('fields', $normalizer->props());

		$sections = $normalizer->sections();

		$this->assertSame(['main-fields'], array_keys($sections));
		$this->assertSame('fields', $sections['main-fields']['type']);
	}

	public function testConvertSectionsToColumns(): void
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
		$this->assertSame(['pages'], array_keys($columns[0]['sections']));
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

		$fields = $normalizer->fields();

		$this->assertSame(['title'], array_keys($fields));
		$this->assertSame('text', $fields['title']['type']);
		$this->assertSame('Title', $fields['title']['label']);
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

		$field = $normalizer->fields()['nonsense'];

		$this->assertSame('info', $field['type']);
		$this->assertSame('Error', $field['label']);
		$this->assertSame(
			'Referenced field "nonsense" is not defined in fields',
			$field['text']
		);
	}

	public function testFields(): void
	{
		$normalizer = $this->normalizer([
			'fields' => [
				'title' => ['type' => 'text'],
				'date'  => ['type' => 'date']
			]
		]);

		$fields = $normalizer->fields();

		$this->assertSame(['title', 'date'], array_keys($fields));
		$this->assertSame('Title', $fields['title']['label']);
		$this->assertSame('1/1', $fields['title']['width']);
		$this->assertSame('date', $fields['date']['type']);
	}

	public function testFieldsEmpty(): void
	{
		$this->assertSame([], $this->normalizer()->fields());
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

		$field = $normalizer->fields()['title'];

		$this->assertSame('info', $field['type']);
		$this->assertSame('negative', $field['theme']);
		$this->assertSame(
			'The field <strong>"title"</strong> already exists in your blueprint',
			$field['text']
		);
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
				'files' => ['type' => 'files']
			]
		]);

		$this->assertSame(['pages', 'files'], array_keys($normalizer->sections()));
	}

	public function testSectionsEmpty(): void
	{
		$this->assertSame([], $this->normalizer()->sections());
	}

	public function testSectionsWithFalse(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'pages' => ['type' => 'pages'],
				'files' => false
			]
		]);

		$this->assertSame(['pages'], array_keys($normalizer->sections()));
	}

	public function testSectionsWithInvalidType(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'test' => ['type' => []]
			]
		]);

		$section = $normalizer->sections()['test'];

		$this->assertSame('info', $section['type']);
		$this->assertSame(
			'Invalid section type for section "test"',
			$section['label']
		);
	}

	public function testSectionsWithTrue(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'pages' => true
			]
		]);

		$this->assertSame('pages', $normalizer->sections()['pages']['type']);
	}

	public function testSectionsWithTypeFromName(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'pages' => []
			]
		]);

		$this->assertSame('pages', $normalizer->sections()['pages']['type']);
	}

	public function testSectionsWithUnknownType(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'test' => ['type' => 'nonsense']
			]
		]);

		$section = $normalizer->sections()['test'];

		$this->assertSame('info', $section['type']);
		$this->assertSame('Invalid section type ("nonsense")', $section['label']);
	}

	public function testSectionsWithoutFields(): void
	{
		$normalizer = $this->normalizer([
			'sections' => [
				'content' => ['type' => 'fields']
			]
		]);

		$this->assertSame([
			'main-info' => [
				'label' => 'Fields',
				'text'  => 'No fields yet',
				'type'  => 'info'
			]
		], $normalizer->sections()['content']['fields']);
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
		$this->assertSame('/pages/a/?tab=content', $tab['link']);
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
