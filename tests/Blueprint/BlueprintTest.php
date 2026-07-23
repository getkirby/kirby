<?php

namespace Kirby\Blueprint;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(Blueprint::class)]
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
				'width'    => '1/3',
				'sections' => []
			],
			[
				'width'    => '2/3',
				'sections' => []
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
						'sections' => [
							'main-info-0' => [
								'label' => 'Column (1/3)',
								'type'  => 'info',
								'text'  => 'No sections yet',
								'name'  => 'main-info-0'
							]
						]
					],
					[
						'width' => '2/3',
						'sections' => [
							'main-info-1' => [
								'label' => 'Column (2/3)',
								'type'  => 'info',
								'text'  => 'No sections yet',
								'name'  => 'main-info-1'
							]
						]
					]
				],
				'icon'    => null,
				'label'   => 'Main',
				'name'    => 'main'
			]
		];

		// the raw props stay free of model-specific data ...
		$this->assertSame($expected, $blueprint->toArray()['tabs']);

		// ... while reading a tab resolves the model link
		$this->assertSame(
			[...$expected['main'], 'link' => '/pages/a/?tab=main'],
			$blueprint->tab()
		);
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

	public function testSectionsToColumns(): void
	{
		$sections = [
			'pages' => [
				'name' => 'pages',
				'type' => 'pages'
			],
			'files' => [
				'name' => 'files',
				'type' => 'files'
			]
		];

		$blueprint = new Blueprint([
			'model'    => $this->model,
			'sections' => $sections
		]);

		$expected = [
			'main' => [
				'name'    => 'main',
				'label'   => 'Main',
				'columns' => [
					[
						'width'    => '1/1',
						'sections' => $sections
					]
				],
				'icon'    => null
			]
		];

		$this->assertEquals($expected, $blueprint->toArray()['tabs']); // cannot use strict assertion (array order)
	}

	public function testFieldsToSections(): void
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
						'width'    => '1/1',
						'sections' => [
							'main-fields' => [
								'name'   => 'main-fields',
								'type'   => 'fields',
								'fields' => $fields
							]
						]
					]
				],
				'icon'    => null
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

	public function testTitleInheritedThroughExtends(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/base'    => ['title' => 'Inherited Title'],
				'pages/derived' => ['extends' => 'pages/base']
			]
		]);

		// the title fallback must not mask a title that is
		// inherited through `extends`
		$blueprint = PageBlueprint::factory(
			new Page(['slug' => 'test']),
			'pages/derived'
		);

		$this->assertSame('Inherited Title', $blueprint->title());
	}

	public function testPresetLabelsI18nAcrossModels(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/album' => ['preset' => 'pages']
			],
			'languages' => [
				[
					'code'         => 'en',
					'default'      => true,
					'translations' => ['pages.status.draft' => 'Drafts']
				],
				[
					'code'         => 'de',
					'translations' => ['pages.status.draft' => 'Entwürfe']
				]
			]
		]);

		$headline = function (string $slug) {
			$page = new Page(['slug' => $slug, 'template' => 'album']);
			return $page->blueprint()->section('drafts')->headline();
		};

		$app->setCurrentTranslation('en');
		$this->assertSame('Drafts', $headline('a'));

		// the second page reuses the normalized props of the first one,
		// which must not freeze the labels to the first language
		$app->setCurrentTranslation('de');
		$this->assertSame('Entwürfe', $headline('b'));
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
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => ['title' => 'Test']
			]
		]);

		$blueprint = Blueprint::factory(new Page(['slug' => 'test']), 'pages/test');

		$this->assertSame('Test', $blueprint->title());
		$this->assertSame('pages/test', $blueprint->name());
	}

	public function testFactoryWithCallbackArray(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => fn () => ['title' => 'Test']
			]
		]);

		$blueprint = Blueprint::factory(new Page(['slug' => 'test']), 'pages/test');

		$this->assertSame('Test', $blueprint->title());
		$this->assertSame('pages/test', $blueprint->name());
	}

	public function testFactoryWithCallbackString(): void
	{
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

		$blueprint = Blueprint::factory(new Page(['slug' => 'test']), 'pages/test');

		$this->assertSame('Test', $blueprint->title());
		$this->assertSame('pages/test', $blueprint->name());
	}

	public function testFactoryForMissingBlueprint(): void
	{
		$blueprint = Blueprint::factory(new Page(['slug' => 'test']), 'notFound');
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

		$this->assertSame('Last', $blueprint->field('MIXEDCASING')['label']);
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
			$sections = $blueprint->tab('main')['columns'][0]['sections'];
		} catch (Exception $e) {
			$this->assertNull($e->getMessage(), 'Failed to get sections.');
		}

		$this->assertIsArray($sections);
		$this->assertCount(1, $sections);
		$this->assertArrayHasKey('main', $sections);
		$this->assertArrayHasKey('label', $sections['main']);
		$this->assertSame('Invalid section type for section "main"', $sections['main']['label']);
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

		$this->assertSame('info', $blueprint->sections()['info']->type());

		// by just passing true
		$blueprint = new Blueprint([
			'model' => $this->model,
			'sections' => [
				'info' => true
			]
		]);

		$this->assertSame('info', $blueprint->sections()['info']->type());
	}

	public function testSectionFromField(): void
	{
		// with options
		$blueprint = new Blueprint([
			'model' => $this->model,
			'fields' => [
				'info' => [
					'type'    => 'section',
					'section' => 'info'
				]
			]
		]);

		$this->assertSame('info', $blueprint->section('info')->type());
	}

	public function testSectionFromFieldWithDifferentName(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'notes' => [
					'type'    => 'section',
					'section' => 'info'
				]
			]
		]);

		$section = $blueprint->section('notes');

		$this->assertSame('info', $section->type());
		$this->assertSame('notes', $section->name());
	}

	public function testSectionFromFieldWithLowercaseName(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'infoBox' => [
					'type'    => 'section',
					'section' => 'info'
				]
			]
		]);

		$section = $blueprint->section('infobox');

		$this->assertSame('info', $section->type());
		$this->assertSame('infobox', $section->name());
	}

	public function testSectionFromFieldWithMissingSectionType(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'info' => [
					'type' => 'section'
				]
			]
		]);

		// the field name is used as fallback for the section type
		$this->assertSame('info', $blueprint->section('info')->type());
	}

	public function testSectionFromFieldWithMissingField(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
		]);

		$this->assertNull($blueprint->section('info'));
	}

	public function testSectionFromFieldWithModel(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'info' => [
					'type'    => 'section',
					'section' => 'info'
				]
			]
		]);

		$this->assertSame($this->model, $blueprint->section('info')->model());
	}

	public function testSectionFromFieldWithNonSectionField(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'info' => [
					'type' => 'text'
				]
			]
		]);

		$this->assertNull($blueprint->section('info'));
	}

	public function testSectionFromFieldWithProps(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'info' => [
					'type'    => 'section',
					'section' => 'info',
					'label'   => 'Notes',
					'text'    => 'Some info',
					'theme'   => 'negative'
				]
			]
		]);

		$section = $blueprint->section('info')->toArray();

		$this->assertSame('Notes', $section['label']);
		$this->assertSame('<p>Some info</p>', trim($section['text']));
		$this->assertSame('negative', $section['theme']);
	}

	public function testSectionFromFieldWithAutomaticLabel(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'infoBox' => [
					'type'    => 'section',
					'section' => 'info'
				]
			]
		]);

		// the automatic field label is passed on to the section
		$this->assertSame(
			'Info box',
			$blueprint->section('infoBox')->toArray()['label']
		);
	}

	public function testSectionFromFieldWithSectionOfSameName(): void
	{
		$blueprint = new Blueprint([
			'model'    => $this->model,
			'fields'   => [
				'info' => [
					'type'    => 'section',
					'section' => 'info',
					'text'    => 'From the field'
				]
			],
			'sections' => [
				'info' => [
					'type' => 'info',
					'text' => 'From the section'
				]
			]
		]);

		// the section definition takes precedence over the field
		$this->assertSame(
			'<p>From the section</p>',
			trim($blueprint->section('info')->toArray()['text'])
		);
	}

	public function testSectionsFromFields(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'text'   => [
					'type' => 'text'
				],
				'drafts' => [
					'type'    => 'section',
					'section' => 'pages',
					'status'  => 'drafts'
				],
				'listed' => [
					'type'    => 'section',
					'section' => 'pages',
					'status'  => 'listed'
				]
			]
		]);

		$sections = $blueprint->sections();

		// the fields section and both section fields
		$this->assertSame(
			['main-fields', 'drafts', 'listed'],
			array_keys($sections)
		);

		// each section keeps its own field name
		$this->assertSame('drafts', $sections['drafts']->name());
		$this->assertSame('listed', $sections['listed']->name());
		$this->assertSame('pages', $sections['drafts']->type());
		$this->assertSame('pages', $sections['listed']->type());
	}

	public function testSectionsFromFieldsInGroup(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'fields' => [
				'group' => [
					'type'   => 'group',
					'fields' => [
						'drafts' => [
							'type'    => 'section',
							'section' => 'pages'
						]
					]
				]
			]
		]);

		$this->assertArrayHasKey('drafts', $blueprint->sections());
	}

	public function testPreset(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'preset' => 'page'
		]);

		$preset = $blueprint->toArray();

		$this->assertSame('page', $preset['preset']);
		$this->assertSame('default', $preset['name']);
		$this->assertSame('Default', $preset['title']);
		$this->assertArrayHasKey('tabs', $preset);
		$this->assertArrayHasKey('main', $preset['tabs']);
		$this->assertNull($preset['tabs']['main']['icon']);
		$this->assertArrayHasKey('columns', $preset['tabs']['main']);
		$this->assertSame('Main', $preset['tabs']['main']['label']);
		$this->assertSame('main', $preset['tabs']['main']['name']);
	}

	public function testTabsAddTheModelLinkAndTranslateTheLabel(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'tabs'  => [
				'main' => ['label' => 'Main', 'fields' => []]
			]
		]);

		// the raw props stay free of model- and language-specific data
		$this->assertArrayNotHasKey('link', $blueprint->toArray()['tabs']['main']);

		// reading a tab resolves both
		$this->assertSame('/pages/a/?tab=main', $blueprint->tabs()[0]['link']);
		$this->assertSame('Main', $blueprint->tabs()[0]['label']);
		$this->assertSame('/pages/a/?tab=main', $blueprint->tab('main')['link']);
		$this->assertSame('/pages/a/?tab=main', $blueprint->tab()['link']);
		$this->assertNull($blueprint->tab('does-not-exist'));
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

		$this->assertSame('Content tab', $blueprint->tabs()[0]['label']);
	}
}
