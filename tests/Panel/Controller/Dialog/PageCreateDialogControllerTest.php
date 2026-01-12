<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Page;
use Kirby\Content\MemoryStorage;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\TestCase;
use Kirby\Uuid\PageUuid;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelCreateDialogController::class)]
#[CoversClass(PageCreateDialogController::class)]
class PageCreateDialogControllerTest extends TestCase
{
	public function testCoreFields(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$fields     = $controller->coreFields();

		$this->assertCount(7, $fields);
		$this->assertSame('Title', $fields['title']['label']);
		$this->assertSame('/', $fields['slug']['path']);
		$this->assertTrue($fields['uuid']['hidden']);
	}

	public function testCoreFieldsUuidDisabled(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'content.uuid' => false
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$fields     = $controller->coreFields();

		$this->assertCount(6, $fields);
		$this->assertSame('Title', $fields['title']['label']);
		$this->assertSame('/', $fields['slug']['path']);
	}

	public function testCoreFieldWithoutTitleSlug(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'title' => 'A simple title',
						'slug'  => 'a-simple-slug'
					]
				]
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$fields     = $controller->coreFields();

		$this->assertArrayNotHasKey('title', $fields);
		$this->assertArrayNotHasKey('slug', $fields);
	}

	public function testCoreFieldInvalidTitleSlug(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'title' => false
					]
				]
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Page create dialog: title and slug must not be false');

		$controller->coreFields();
	}

	public function testCustomFieldWithUnknownField(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'create' => [
						'fields' => [
							'notthere'
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageCreateDialogController();
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Unknown field  "notthere" in create dialog');

		$controller->customFields();
	}

	public function testCustomFieldWithUnsupportedField(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'create' => [
						'fields' => [
							'foo'
						]
					],
					'fields' => [
						'foo' => [
							'type' => 'files',
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageCreateDialogController();
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Field type "files" not supported in create dialog');

		$controller->customFields();
	}

	public function testCustomFieldWithForbiddenField(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'create' => [
						'fields' => [
							'slug'
						]
					],
					'fields' => [
						'slug' => [
							'type' => 'text',
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageCreateDialogController();
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Field name "slug" not allowed as custom field in create dialog');

		$controller->customFields();
	}

	public function testFactory(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'request' => [
				'query' => [
					'view' => 'pages/test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = PageCreateDialogController::factory();
		$this->assertSame('test', $controller->parent->id());
	}

	public function testLoad(): void
	{
		$controller = new PageCreateDialogController();
		$dialog     = $controller->load();
		$this->assertSame('k-page-create-dialog', $dialog->component);

		$props = $dialog->props();
		$this->assertSame('Title', $props['fields']['title']['label']);
		$this->assertSame('URL appendix', $props['fields']['slug']['label']);
		$this->assertSame('title', $props['fields']['slug']['sync']);

		// there's only the default template for now
		$this->assertTrue($props['fields']['template']['hidden']);

		$this->assertSame('Create as Draft', $props['submitButton']);

		$this->assertSame('', $props['value']['slug']);
		$this->assertSame('default', $props['value']['template']);
		$this->assertSame('', $props['value']['title']);
	}

	public function testLoadWithMultipleBlueprints(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/a' => ['title' => 'A'],
				'pages/b' => ['title' => 'B'],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageCreateDialogController();
		$props      = $controller->load()->props();

		// a + b + default
		$this->assertCount(3, $props['blueprints']);
	}

	public function testLoadWithCustomTitleLabel(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'create' => [
						'title' => [
							'label' => $label = 'Just a simple label'
						]
					]
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageCreateDialogController();
		$props      = $controller->load()->props();

		$this->assertSame($label, $props['fields']['title']['label']);
	}

	public function testLoadWithI18nTitleLabel(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'create' => [
						'title' => [
							'label' => [
								'en' => $label = 'English label',
								'de' => 'German label'
							]
						]
					]
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageCreateDialogController();
		$props      = $controller->load()->props();

		$this->assertSame($label, $props['fields']['title']['label']);
	}

	public function testLoadWithCustomFields(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'create' => [
						'fields' => [
							'foo'
						]
					],
					'fields' => [
						'foo' => [
							'type' => 'select',
							'options' => [
								'type' => 'array',
								'options' => [
									'a' => 'A',
									'b' => 'B'
								],
								'width' => '1/2'
							]
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageCreateDialogController();
		$props      = $controller->load()->props();

		$this->assertArrayHasKey('foo', $props['fields']);
		$this->assertSame('1/1', $props['fields']['foo']['width']);
	}

	public function testModel(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'fields' => ['foo', 'bar']
					],
					'fields' => [
						'foo' => [
							'type'     => 'text',
							'required' => true
						],
						'bar' => [
							'type'     => 'text',
							'required' => true,
							'default'  => 'bar'
						]
					]
				]
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$model      = $controller->model();

		$this->assertInstanceOf(Page::class, $model);
		$this->assertInstanceOf(MemoryStorage::class, $model->storage());
		$this->assertInstanceOf(PageUuid::class, $model->uuid());
	}

	public function testModelUuidDisabled(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'content.uuid' => false
			],
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'fields' => ['foo', 'bar']
					],
					'fields' => [
						'foo' => [
							'type'     => 'text',
							'required' => true
						],
						'bar' => [
							'type'     => 'text',
							'required' => true,
							'default'  => 'bar'
						]
					]
				]
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$model      = $controller->model();

		$this->assertInstanceOf(Page::class, $model);
		$this->assertInstanceOf(MemoryStorage::class, $model->storage());
		$this->assertNull($model->uuid());
	}

	public function testResolveFieldTemplates(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'title' => 'This is a {{ page.foo }}',
						'slug'  => 'page-{{ page.bar }}'
					]
				]
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$input      = $controller->resolveFieldTemplates([
			'foo' => 'Foo',
			'bar' => 'foo',
		], ['title', 'slug']);

		$this->assertSame([
			'foo'   => 'Foo',
			'bar'   => 'foo',
			'title' => 'This is a Foo',
			'slug'  => 'page-foo',
		], $input);
	}

	public function testSanitize(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'content.uuid' => false
			],
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'fields' => ['foo', 'bar']
					],
					'fields' => [
						'foo' => [
							'type'     => 'text',
							'required' => true
						],
						'bar' => [
							'type'     => 'text',
							'required' => true,
							'default'  => 'bar'
						]
					]
				]
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$input      = $controller->sanitize([
			'slug'  => 'foo',
			'title' => 'Foo',
			'foo'   => 'bar',
			'uuid'  => 'test-uuid'
		]);

		$this->assertSame([
			'content'  => [
				'foo'   => 'bar',
				'bar'   => 'bar',
				'title' => 'Foo',
				'uuid'  => 'test-uuid',
			],
			'slug'     => 'foo',
			'template' => 'test',
		], $input);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'Test',
					'slug'  => 'test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$this->assertNull($this->app->page('test'));

		$controller = new PageCreateDialogController();
		$response   = $controller->submit();

		$this->assertSame('page.create', $response['event']);
		$this->assertSame('test', $this->app->page('test')->slug());
		$this->assertSame('Test', $this->app->page('test')->title()->value());
		$this->assertSame('draft', $this->app->page('test')->status());
	}

	public function testSubmitWithParent(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'request' => [
				'query' => [
					'title' => 'Test',
					'slug'  => 'test-child'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = $this->app->page('test/test-child');
		$this->assertNull($page);

		$parent     = $this->app->page('test');
		$controller = new PageCreateDialogController(parent: $parent);
		$response   = $controller->submit();

		$this->assertSame('page.create', $response['event']);

		$page = $this->app->page('test/test-child');
		$this->assertSame('test-child', $page->slug());
		$this->assertSame('Test', $page->title()->value());
	}

	public function testSubmitWithCustomField(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'create' => [
						'fields' => [
							'foo'
						]
					],
					'fields' => [
						'foo' => [
							'type' => 'text',
						]
					]
				]
			],
			'request' => [
				'query' => [
					'title' => 'Test',
					'slug'  => 'test',
					'foo'   => 'bar',
					'homer' => 'simpson'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = PageCreateDialogController::factory();
		$response   = $controller->submit();

		$this->assertSame('/pages/test', $response['redirect']);
		$this->assertSame('bar', $this->app->page('test')->foo()->value());
		$this->assertNull($this->app->page('test')->homer()->value());
	}

	public function testSubmitWithoutTitle(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'slug' => 'test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		$controller = PageCreateDialogController::factory();
		$controller->submit();
	}

	public function testSubmitWithCustomStatus(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'create' => [
						'status' => 'unlisted'
					]
				]
			],
			'request' => [
				'query' => [
					'title' => 'Test',
					'slug'  => 'test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = PageCreateDialogController::factory();
		$response   = $controller->submit();

		$this->assertSame('page.create', $response['event']);
		$this->assertSame('unlisted', $this->app->page('test')->status());
	}

	public function testSubmitWithTitleSlugFieldsTemplates(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'fields' => [
						'foo' => [
							'type'    => 'text',
							'default' => '{{ page.title }}'
						],
						'bar' => [
							'type'    => 'text',
							'default' => '{{ page.slug }}'
						]
					]
				]
			],
			'request' => [
				'query' => [
					'title' => 'Foo title',
					'slug'  => 'bar-slug'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = PageCreateDialogController::factory();
		$controller->submit();

		$page = $this->app->page('bar-slug');

		$this->assertSame('Foo title', $page->title()->value());
		$this->assertSame('Foo title', $page->foo()->value());
		$this->assertSame('bar-slug', $page->slug());
		$this->assertSame('bar-slug', $page->bar()->value());
	}

	public function testValidateInvalidTitle(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		$controller = new PageCreateDialogController();
		$controller->validate(['content' => ['title' => '']]);
	}

	public function testValidateInvalidSlug(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		$controller = new PageCreateDialogController();
		$controller->validate([
			'slug'    => '',
			'content' => ['title' => 'Foo']
		]);
	}

	public function testValidateInvalidFields(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeStatus.incomplete');

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'foo' => [
							'type' => 'text',
							'required' => true
						]
					]
				]
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$controller->validate([
			'slug'    => 'foo',
			'content' => ['title' => 'Foo']
		], 'listed');
	}

	public function testValidateValidFields(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'foo' => [
							'type'     => 'text',
							'required' => true
						]
					]
				]
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$valid      = $controller->validate([
			'slug'    => 'foo',
			'content' => ['title' => 'Foo', 'foo' => 'bar']
		], 'listed');

		$this->assertTrue($valid);
	}

	public function testValue(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'fields' => ['foo']
					],
					'fields' => [
						'foo' => [
							'type'     => 'text',
							'required' => true,
							'default'  => 'bar'
						]
					]
				]
			],
			'request' => [
				'query' => [
					'template' => 'test'
				]
			]
		]);

		$controller = new PageCreateDialogController();
		$value      = $controller->value();

		$this->assertSame('', $value['slug']);
		$this->assertSame('test', $value['template']);
		$this->assertSame('', $value['title']);
		$this->assertNotNull($value['uuid']);
		$this->assertSame('bar', $value['foo']);
	}
}
