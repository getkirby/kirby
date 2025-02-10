<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Blueprint::class)]
class BlueprintTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Blueprint';

	protected ModelWithContent $model;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->model = new Page(['slug' => 'a']);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testConstructWithoutModel()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('A blueprint model is required');

		new Blueprint([]);
	}

	public function testConstructInvalidModel()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid blueprint model');

		new Blueprint(['model' => new \stdClass()]);
	}

	public function testConvertColumnsToTabs()
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
				'link'    => '/pages/a/?tab=main',
				'name'    => 'main'
			]
		];

		$this->assertSame($expected, $blueprint->toArray()['tabs']);
		$this->assertSame($expected['main'], $blueprint->tab());
	}

	public function testAcceptedFileTemplatesDefault()
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'sections' => [
				'files' => [
					'type' => 'files',
				],
			]
		]);

		$this->assertSame(['default'], $blueprint->acceptedFileTemplates());
	}

	public function testAcceptedFileTemplatesFromFields()
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'files/a' => [
					'name' => 'a',
				],
				'files/b' => [
					'name' => 'b',
				],
				'files/c' => [
					'name' => 'c',
				],
				'files/d' => [
					'name' => 'd',
				],
				'files/e' => [
					'name' => 'e',
				],
			]
		]);

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'fields' => [
				'a' => [
					'type' => 'files',
					'uploads' => [
						'template' => 'a'
					]
				],
				'b' => [
					'type' => 'textarea',
					'uploads' => [
						'template' => 'b'
					]
				],
				'c' => [
					'type'   => 'structure',
					'fields' => [
						'text' => [
							'type' => 'textarea',
							'uploads' => [
								'template' => 'c'
							]
						]
					]
				],
				'd' => [
					'type'   => 'object',
					'fields' => [
						'text' => [
							'type' => 'object',
							'uploads' => [
								'template' => 'd'
							]
						]
					]
				],
				'e' => [
					'type' => 'blocks',
					'fieldsets' => [
						'text' => [
							'fields' => [
								'text' => [
									'type' => 'object',
									'uploads' => [
										'template' => 'e'
									]
								]
							]
						]
					]
				]
			]
		]);

		$this->assertSame(['a', 'b', 'c', 'd', 'e'], $blueprint->acceptedFileTemplates());
	}

	public function testAcceptedFileTemplatesFromFieldsAndSections()
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'files/a' => [
					'name' => 'a',
				],
				'files/b' => [
					'name' => 'b',
				],
				'files/c' => [
					'name' => 'c',
				],
			]
		]);

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'sections' => [
				'fields' => [
					'type' => 'fields',
					'fields' => [
						'a' => [
							'type' => 'files',
							'uploads' => [
								'template' => 'a'
							]
						],
						'b' => [
							'type' => 'textarea',
							'uploads' => [
								'template' => 'b'
							]
						],
					],
				],
				'files' => [
					'type'     => 'files',
					'template' => 'c'
				]
			]
		]);

		$this->assertSame(['a', 'b', 'c'], $blueprint->acceptedFileTemplates());
	}

	public function testAcceptedFileTemplatesFromSection()
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'files/a' => [
					'name' => 'a',
				]
			]
		]);

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'sections' => [
				'a' => [
					'type'     => 'files',
					'template' => 'a'
				],
			]
		]);

		$this->assertSame(['a'], $blueprint->acceptedFileTemplates('a'));
	}

	public function testAcceptedFileTemplatesFromSections()
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'files/a' => [
					'name' => 'a',
				],
				'files/b' => [
					'name' => 'b',
				],
			]
		]);

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'sections' => [
				'a' => [
					'type'     => 'files',
					'template' => 'a'
				],
				'b' => [
					'type'     => 'files',
					'template' => 'b'
				]
			]
		]);

		$this->assertSame(['a', 'b'], $blueprint->acceptedFileTemplates());
	}

	public function testButtons()
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'buttons' => ['foo', 'bar']
		]);

		$this->assertSame(['foo', 'bar'], $blueprint->buttons());
	}

	public function testButtonsDisabled()
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'buttons' => false
		]);

		$this->assertSame(false, $blueprint->buttons());
	}

	public function testDebugInfo()
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

	public function testSectionsToColumns()
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
				'icon'    => null,
				'link'    => '/pages/a/?tab=main'
			]
		];

		$this->assertEquals($expected, $blueprint->toArray()['tabs']); // cannot use strict assertion (array order)
	}

	public function testFieldsToSections()
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
				'icon'    => null,
				'link'    => '/pages/a/?tab=main'
			]
		];

		$this->assertEquals($expected, $blueprint->toArray()['tabs']); // cannot use strict assertion (array order)
	}

	public function testTitle()
	{
		$blueprint = new Blueprint([
			'title' => 'Test',
			'model' => $this->model
		]);

		$this->assertSame('Test', $blueprint->title());
	}

	public function testTitleTranslated()
	{
		$blueprint = new Blueprint([
			'title' => ['en' => 'Test'],
			'model' => $this->model
		]);

		$this->assertSame('Test', $blueprint->title());
	}

	public function testTitleTranslatedFallback()
	{
		I18n::$locale       = 'de';
		I18n::$translations = ['en' => ['my.i18n.string' => 'success']];

		$blueprint = new Blueprint([
			'title' => 'my.i18n.string',
			'model' => $this->model
		]);

		$this->assertSame('success', $blueprint->title());
	}

	public function testTitleTranslatedFallbackForRoles()
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

	public function testTitleFromName()
	{
		$blueprint = new Blueprint([
			'model' => $this->model
		]);

		$this->assertSame('Default', $blueprint->title());

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'test'
		]);

		$this->assertSame('Test', $blueprint->title());
	}

	public function testExtend()
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

	public function testExtendWithInvalidSnippet()
	{
		$blueprint = new Blueprint([
			'extends' => 'notFound',
			'model'   => new Page(['slug' => 'test'])
		]);

		$this->assertSame('Default', $blueprint->title());
	}

	public function testExtendMultiple()
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

	public function testFactory()
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

	public function testFactoryWithCallbackArray()
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

	public function testFactoryWithCallbackString()
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

	public function testFactoryForMissingBlueprint()
	{
		$blueprint = Blueprint::factory('notFound', null, new Page(['slug' => 'test']));
		$this->assertNull($blueprint);
	}

	public function testFields()
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

	public function testNestedFields()
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

	public function testInvalidSectionType()
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
		} catch (\Exception $e) {
			$this->assertNull($e->getMessage(), 'Failed to get sections.');
		}

		$this->assertIsArray($sections);
		$this->assertCount(1, $sections);
		$this->assertArrayHasKey('main', $sections);
		$this->assertArrayHasKey('label', $sections['main']);
		$this->assertSame('Invalid section type for section "main"', $sections['main']['label']);
	}

	public function testIsDefault()
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default'
		]);

		$this->assertTrue($blueprint->isDefault());
	}

	public function testSectionTypeFromName()
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

	public function testPreset()
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
		$this->assertSame('/pages/a/?tab=main', $preset['tabs']['main']['link']);
		$this->assertSame('main', $preset['tabs']['main']['name']);
	}
}
