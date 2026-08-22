<?php

namespace Kirby\Blueprint;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Form\Fields;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(Blueprint::class)]
#[CoversClass(Normalizer::class)]
class BlueprintTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.Blueprint';

	protected ModelWithContent $model;

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->model = new Page(['slug' => 'a']);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testAcceptRules(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
		]);

		$this->assertInstanceOf(AcceptRules::class, $blueprint->acceptRules());
	}

	public function testConstructWithoutModel(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('A blueprint model is required');

		new Blueprint([]);
	}

	public function testConstructInvalidModel(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid blueprint model');

		new Blueprint(['model' => new stdClass()]);
	}

	public function testConvertColumnsToTabs(): void
	{
		$columns = [
			[
				'width'  => '1/3',
				'fields' => []
			],
			[
				'width'  => '2/3',
				'fields' => []
			]
		];

		$blueprint = new Blueprint([
			'model'   => $this->model,
			'columns' => $columns
		]);

		$expected = [
			'main' => [
				'columns' => [
					[
						'width' => '1/3',
						'fields' => [
							'main-info-0' => [
								'label' => 'Column (1/3)',
								'type'  => 'info',
								'text'  => 'No fields yet',
								'name'  => 'main-info-0',
								'width' => '1/1'
							]
						]
					],
					[
						'width' => '2/3',
						'fields' => [
							'main-info-1' => [
								'label' => 'Column (2/3)',
								'type'  => 'info',
								'text'  => 'No fields yet',
								'name'  => 'main-info-1',
								'width' => '1/1'
							]
						]
					]
				],
				'icon'    => null,
				'label'   => 'Main',
				'link'    => '/pages/a/?tab=main',
				'name'    => 'main'
			]
		];

		$this->assertSame($expected, $blueprint->toArray()['tabs']);
		$this->assertSame($expected['main'], $blueprint->tab()->toViewProps());
	}

	public function testButtons(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'buttons' => ['foo', 'bar']
		]);

		$this->assertSame(['foo', 'bar'], $blueprint->buttons());
	}

	public function testButtonsDisabled(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'buttons' => false
		]);

		$this->assertSame(false, $blueprint->buttons());
	}

	public function testDebugInfo(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default'
		]);

		$expected = [
			'name'  => 'default',
			'title' => 'Default',
			'tabs'  => []
		];

		$this->assertSame($expected, $blueprint->__debugInfo());
	}

	public function testSectionsToFields(): void
	{
		$blueprint = new Blueprint([
			'model'    => $this->model,
			'sections' => [
				'pages' => [
					'type' => 'pages'
				],
				'files' => [
					'type' => 'files'
				]
			]
		]);

		$expected = [
			'main' => [
				'name'    => 'main',
				'label'   => 'Main',
				'columns' => [
					[
						'width'  => '1/1',
						'fields' => [
							'pages' => [
								'label' => 'Pages',
								'name'  => 'pages',
								'type'  => 'pagelist',
								'width' => '1/1'
							],
							'files' => [
								'label' => 'Files',
								'name'  => 'files',
								'type'  => 'filelist',
								'width' => '1/1'
							]
						]
					]
				],
				'icon'    => null,
				'link'    => '/pages/a/?tab=main'
			]
		];

		$this->assertEquals($expected, $blueprint->toArray()['tabs']); // cannot use strict assertion (array order)
	}

	public function testSectionsToFieldsWithHeadline(): void
	{
		$blueprint = new Blueprint([
			'model'    => $this->model,
			'sections' => [
				'pages' => [
					'headline' => 'My pages',
					'type'     => 'pages'
				]
			]
		]);

		$field = $blueprint->field('pages');

		$this->assertSame('pagelist', $field['type']);
		$this->assertSame('My pages', $field['label']);
		$this->assertArrayNotHasKey('headline', $field);
	}


	public function testSectionsToFieldsWithUnknownType(): void
	{
		$blueprint = new Blueprint([
			'model'    => $this->model,
			'sections' => [
				'mysection' => [
					'type' => 'does-not-exist'
				]
			]
		]);

		$field = $blueprint->field('mysection');

		$this->assertSame('info', $field['type']);
		$this->assertSame('negative', $field['theme']);
		$this->assertSame('Invalid section type ("does-not-exist")', $field['label']);
		$this->assertSame(
			'The following section types are available: ' . PHP_EOL .
			'- *fields*' . PHP_EOL .
			'- *files*' . PHP_EOL .
			'- *info*' . PHP_EOL .
			'- *pages*' . PHP_EOL .
			'- *stats*',
			$field['text']
		);
	}

	public function testFieldsSectionWithWhen(): void
	{
		$blueprint = new Blueprint([
			'model'    => $this->model,
			'sections' => [
				'mysection' => [
					'type'   => 'fields',
					'when'   => ['toggle' => true],
					'fields' => [
						'a' => [
							'type' => 'text'
						],
						'b' => [
							'type' => 'text',
							'when' => ['other' => true]
						]
					]
				]
			]
		]);

		// the condition of the section is pushed down onto its fields
		$this->assertSame(['toggle' => true], $blueprint->field('a')['when']);

		// while an own condition of a field wins
		$this->assertSame(
			['toggle' => true, 'other' => true],
			$blueprint->field('b')['when']
		);
	}

	public function testFieldsSectionWithInvalidFields(): void
	{
		$blueprint = new Blueprint([
			'model'    => $this->model,
			'sections' => [
				'mysection' => [
					'type'   => 'fields',
					'fields' => 'nonsense'
				]
			]
		]);

		$fields = $blueprint->tab('main')->columns()[0]['fields'];

		// the section is unwrapped into no fields at all,
		// which leaves the column with the guide field
		$this->assertSame(['main-info-0'], array_keys($fields));
		$this->assertSame('No fields yet', $fields['main-info-0']['text']);
	}

	public function testFieldsToColumns(): void
	{
		$fields = [
			'headline' => [
				'label' => 'Headline',
				'name'  => 'headline',
				'type'  => 'text',
				'width' => '1/1'
			]
		];

		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => $fields
		]);

		$expected = [
			'main' => [
				'name'    => 'main',
				'label'   => 'Main',
				'columns' => [
					[
						'width'  => '1/1',
						'fields' => $fields
					]
				],
				'icon'    => null,
				'link'    => '/pages/a/?tab=main'
			]
		];

		$this->assertEquals($expected, $blueprint->toArray()['tabs']); // cannot use strict assertion (array order)
	}

	public function testTitle(): void
	{
		$blueprint = new Blueprint([
			'title' => 'Test',
			'model' => $this->model
		]);

		$this->assertSame('Test', $blueprint->title());
	}

	public function testTitleTranslated(): void
	{
		$blueprint = new Blueprint([
			'title' => ['en' => 'Test'],
			'model' => $this->model
		]);

		$this->assertSame('Test', $blueprint->title());
	}

	public function testTitleTranslatedFallback(): void
	{
		I18n::$locale       = 'de';
		I18n::$translations = ['en' => ['my.i18n.string' => 'success']];

		$blueprint = new Blueprint([
			'title' => 'my.i18n.string',
			'model' => $this->model
		]);

		$this->assertSame('success', $blueprint->title());
	}

	public function testTitleTranslatedFallbackForRoles(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true,
					'translations' => [
						'my.custom.role' => 'My custom role'
					]
				],
				[
					'code' => 'de',
					'translations' => []
				]
			],
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'title' => 'my.custom.role'
				]
			]
		]);

		$app->setCurrentTranslation('de');
		$app->setCurrentLanguage('de');

		$role = $app->roles()->get('editor')->title();
		$this->assertSame('My custom role', $role);
	}

	public function testExtend(): void
	{
		new App([
			'blueprints' => [
				'test' => [
					'title' => 'Extension Test'
				]
			]
		]);

		$blueprint = new Blueprint([
			'extends' => 'test',
			'model'   => new Page(['slug' => 'test'])
		]);

		$this->assertSame('Extension Test', $blueprint->title());
	}

	public function testExtendWithInvalidSnippet(): void
	{
		$blueprint = new Blueprint([
			'extends' => 'notFound',
			'model'   => new Page(['slug' => 'test'])
		]);

		$this->assertSame('Default', $blueprint->title());
	}

	public function testExtendMultiple(): void
	{
		new App([
			'blueprints' => [
				'props/after' => ['after' => 'foo'],
				'props/before' => ['before' => 'bar'],
				'props/required' => ['required' => true],
				'props/text' => ['type' => 'text'],
				'props/translatable' => ['translatable' => false],
				'props/width' => ['width' => '1/3']
			]
		]);

		$blueprint = new Blueprint([
			'model' => new Page(['slug' => 'test']),
			'fields' => [
				'test' => [
					'label' => 'Test',
					'extends'  => [
						'props/after',
						'props/before',
						'props/required',
						'props/text',
						'props/translatable',
						'props/width',
					]
				]
			]
		]);

		$field = $blueprint->field('test');

		$this->assertSame('foo', $field['after']);
		$this->assertSame('bar', $field['before']);
		$this->assertTrue($field['required']);
		$this->assertSame('text', $field['type']);
		$this->assertFalse($field['translatable']);
		$this->assertSame('1/3', $field['width']);
	}

	public function testFactory(): void
	{
		Blueprint::$loaded = [];

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => ['title' => 'Test']
			]
		]);

		$blueprint = Blueprint::factory('pages/test', null, new Page(['slug' => 'test']));

		$this->assertSame('Test', $blueprint->title());
		$this->assertSame('pages/test', $blueprint->name());
	}

	public function testFactoryWithCallbackArray(): void
	{
		Blueprint::$loaded = [];

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => fn () => ['title' => 'Test']
			]
		]);

		$blueprint = Blueprint::factory('pages/test', null, new Page(['slug' => 'test']));

		$this->assertSame('Test', $blueprint->title());
		$this->assertSame('pages/test', $blueprint->name());
	}

	public function testFactoryWithCallbackString(): void
	{
		Blueprint::$loaded = [];

		$this->app = $this->app->clone([
			'roots' => [
				'index' => '/dev/null',
				'blueprints' => static::TMP,
			],
			'blueprints' => [
				'pages/test' => fn () => static::TMP . '/custom/test.yml'
			]
		]);

		Data::write(static::TMP . '/custom/test.yml', ['title' => 'Test']);

		$blueprint = Blueprint::factory('pages/test', null, new Page(['slug' => 'test']));

		$this->assertSame('Test', $blueprint->title());
		$this->assertSame('pages/test', $blueprint->name());
	}

	public function testFactoryForMissingBlueprint(): void
	{
		$blueprint = Blueprint::factory('notFound', null, new Page(['slug' => 'test']));
		$this->assertNull($blueprint);
	}

	public function testFields(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'fields' => $fields = [
				'test' => [
					'type'  => 'text',
					'name'  => 'test',
					'label' => 'Test',
					'width' => '1/1'
				]
			]
		]);

		$this->assertSame($fields, $blueprint->fields());
		$this->assertSame($fields['test'], $blueprint->field('test'));
	}

	public function testFieldWithNormalizedName(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'fields' => $fields = [
				'mixedCasing' => [
					'type'  => 'text',
					'name'  => 'mixedCasing',
					'label' => 'Mixed Casing',
					'width' => '1/1'
				]
			]
		]);

		$this->assertSame($fields['mixedCasing'], $blueprint->field('mixedCasing'));
		$this->assertSame($fields['mixedCasing'], $blueprint->field('mixedcasing'));
	}

	public function testFieldWithDuplicateNormalizedName(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'fields' => [
				'mixedCasing' => [
					'type'  => 'text',
					'label' => 'First'
				],
				'MixedCasing' => [
					'type'  => 'textarea',
					'label' => 'Last'
				]
			]
		]);

		// names are lowercased further down the line, so both
		// definitions are the same field and collide with each other
		$this->assertSame('First', $blueprint->field('MIXEDCASING')['label']);
		$this->assertSame('text', $blueprint->field('MIXEDCASING')['type']);

		$error = $blueprint->field('MixedCasing-duplicate-1');

		$this->assertSame('info', $error['type']);
		$this->assertSame(
			'The field <strong>"MixedCasing"</strong> already exists in your blueprint',
			$error['text']
		);
	}

	public function testNestedFields(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'fields' => $fields = [
				'test' => [
					'type'   => 'structure',
					'fields' => [
						'child-field' => [
							'type' => 'text'
						]
					]
				]
			]
		]);

		$this->assertCount(1, $blueprint->fields());
		$this->assertArrayHasKey('test', $blueprint->fields());
		$this->assertArrayNotHasKey('child-field', $blueprint->fields());
	}

	public function testNormalizeOptionsFalse(): void
	{
		$blueprint = new PageBlueprint([
			'model'   => $this->model,
			'options' => false,
		]);

		// all keys from defaults are present and every value is false, not null
		foreach ($blueprint->options() as $key => $value) {
			$this->assertFalse($value, "Option '$key' should be false");
		}
	}

	public function testInvalidSectionType(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'sections' => [
				'main' => [
					'type' => [
						'headline' => [
							'label' => 'Headline',
							'name'  => 'headline',
							'type'  => 'text',
							'width' => '1/1'
						]
					]
				]
			]
		]);

		try {
			$fields = $blueprint->tab('main')->columns()[0]['fields'];
		} catch (Exception $e) {
			$this->assertNull($e->getMessage(), 'Failed to get fields.');
		}

		$this->assertIsArray($fields);
		$this->assertCount(1, $fields);
		$this->assertArrayHasKey('main', $fields);
		$this->assertSame('info', $fields['main']['type']);
		$this->assertSame('Invalid section type for section "main"', $fields['main']['label']);
	}

	public function testIsDefault(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default'
		]);

		$this->assertTrue($blueprint->isDefault());
	}

	public function testSectionTypeFromName(): void
	{
		// with options
		$blueprint = new Blueprint([
			'model' => $this->model,
			'sections' => [
				'info' => [
				]
			]
		]);

		$this->assertSame('info', $blueprint->field('info')['type']);

		// by just passing true
		$blueprint = new Blueprint([
			'model' => $this->model,
			'sections' => [
				'info' => true
			]
		]);

		$this->assertSame('info', $blueprint->field('info')['type']);
	}











	public function testSectionAndFieldOfSameName(): void
	{
		$blueprint = new Blueprint([
			'model'    => $this->model,
			'sections' => [
				'fields' => [
					'type'   => 'fields',
					'fields' => [
						'info' => [
							'type' => 'text'
						]
					]
				],
				'info' => [
					'type' => 'info',
					'text' => 'From the section'
				]
			]
		]);

		// sections and fields share a single namespace, so the first
		// definition keeps the name and the second one is turned
		// into a duplicate name error with a name of its own
		$field = $blueprint->field('info');

		$this->assertSame('text', $field['type']);

		$error = $blueprint->field('info-duplicate-1');

		$this->assertSame('info', $error['type']);
		$this->assertSame('negative', $error['theme']);
		$this->assertSame(
			'The field <strong>"info"</strong> already exists in your blueprint',
			$error['text']
		);
	}

	public function testSectionAndFieldOfSameNameKeepStoredValues(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/note' => [
					'fields' => [
						'shared' => ['type' => 'text'],
						'other'  => ['type' => 'text']
					],
					'sections' => [
						'main' => [
							'type'   => 'fields',
							'fields' => ['shared', 'other']
						],
						'aside' => [
							'type'   => 'fields',
							'fields' => ['shared']
						]
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'note',
						'template' => 'note',
						'content'  => [
							'shared' => 'keepme',
							'other'  => 'old'
						]
					]
				]
			]
		]);

		$page = $app->page('note');

		// the first reference keeps a storable field, so its value
		// survives when an unrelated field is submitted
		$this->assertSame('text', $page->blueprint()->field('shared')['type']);
		$this->assertSame('info', $page->blueprint()->field('shared-duplicate-1')['type']);

		$fields = Fields::for($page);
		$fields->fill(input: $page->content()->toArray());
		$fields->submit(input: ['other' => 'new']);

		$this->assertSame('keepme', $fields->toStoredValues()['shared']);
	}

	public function testSectionAndFieldOfSameNameRenderInTheTab(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/album' => [
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
				]
			],
			'site' => [
				'children' => [
					['slug' => 'album', 'template' => 'album']
				]
			]
		]);

		$page   = $app->page('album');
		$fields = Fields::for($page);

		// the Panel resolves each column from the field registry, so
		// both the surviving field and the error need an entry of their own
		$images = $page->blueprint()->tab('images')->columns($fields);
		$docs   = $page->blueprint()->tab('docs')->columns($fields);

		$this->assertSame(['files'], array_keys($images[0]['fields']));
		$this->assertSame('filelist', $images[0]['fields']['files']['type']);

		$this->assertSame(['files-duplicate-1'], array_keys($docs[0]['fields']));
		$this->assertSame('info', $docs[0]['fields']['files-duplicate-1']['type']);
	}

	public function testAutomaticLabelForFields()
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'fields' => [
				'emailAddress' => [
					'type' => 'email'
				],
			]
		]);

		$this->assertSame('Email address', $blueprint->fields()['emailAddress']['label']);
	}

	public function testAutomaticLabelForTabs()
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'tabs'  => [
				'contentTab' => [

				],
			]
		]);

		$this->assertSame('Content tab', $blueprint->tabs()->first()->label());
	}

	public function testTabs(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'tabs'  => [
				'content'  => [],
				'settings' => []
			]
		]);

		$this->assertInstanceOf(Tabs::class, $blueprint->tabs());
		$this->assertCount(2, $blueprint->tabs());

		// the collection is only created once
		$this->assertSame($blueprint->tabs(), $blueprint->tabs());

		// the first tab is returned without a name
		$this->assertSame($blueprint->tab('content'), $blueprint->tab());

		// tabs are matched case-insensitively
		$this->assertSame($blueprint->tab('content'), $blueprint->tab('Content'));

		$this->assertNull($blueprint->tab('does-not-exist'));
	}

	public function testTabsEmpty(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model
		]);

		$this->assertCount(0, $blueprint->tabs());
		$this->assertNull($blueprint->tab());
	}
}
