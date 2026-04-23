<?php

namespace Kirby\Panel;

use Kirby\Cms\Page;
use Kirby\Content\MemoryStorage;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Areas\AreaTestCase;
use Kirby\Uuid\PageUuid;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageCreateDialog::class)]
class PageCreateDialogTest extends AreaTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	public function testCoreFields(): void
	{
		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$fields = $dialog->coreFields();

		$this->assertCount(7, $fields);
		$this->assertSame('Title', $fields['title']['label']);
		$this->assertSame('/', $fields['slug']['path']);
		$this->assertTrue($fields['uuid']['hidden']);
	}

	public function testCoreFieldsUuidDisabled(): void
	{
		$this->app([
			'options' => [
				'content.uuid' => false
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$fields = $dialog->coreFields();

		$this->assertCount(6, $fields);
		$this->assertSame('Title', $fields['title']['label']);
		$this->assertSame('/', $fields['slug']['path']);
	}

	public function testCoreFieldWithoutTitleSlug(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'title' => 'A simple title',
						'slug'  => 'a-simple-slug'
					]
				]
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$fields = $dialog->coreFields();

		$this->assertArrayNotHasKey('title', $fields);
		$this->assertArrayNotHasKey('slug', $fields);
	}

	public function testCoreFieldInvalidTitleSlug(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'title' => false
					]
				]
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Page create dialog: title and slug must not be false');

		$dialog->coreFields();
	}

	public function testResolveFieldTemplates(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'title' => 'This is a {{ page.foo }}',
						'slug'  => 'page-{{ page.bar }}'
					]
				]
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$input = $dialog->resolveFieldTemplates([
			'foo' => 'Foo',
			'bar' => 'foo',
		]);

		$this->assertSame([
			'foo'   => 'Foo',
			'bar'   => 'foo',
			'title' => 'This is a Foo',
			'slug'  => 'page-foo',
		], $input);
	}

	public function testSanitize(): void
	{
		$this->app([
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
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$input = $dialog->sanitize([
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

	public function testValidateInvalidTitle(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$dialog->validate(['content' => ['title' => '']]);
	}

	public function testValidateInvalidSlug(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$dialog->validate([
			'slug'    => '',
			'content' => ['title' => 'Foo']
		]);
	}

	public function testValidateInvalidFields(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeStatus.incomplete');

		$this->app([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'foo' => [
							'type' => 'text',
							'required' => true
						]
					]
				]
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$dialog->validate([
			'slug'    => 'foo',
			'content' => ['title' => 'Foo']
		], 'listed');
	}

	public function testValidateValidFields(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'foo' => [
							'type'     => 'text',
							'required' => true
						]
					]
				]
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$valid = $dialog->validate([
			'slug'    => 'foo',
			'content' => ['title' => 'Foo', 'foo' => 'bar']
		], 'listed');

		$this->assertTrue($valid);
	}

	public function testValue(): void
	{
		$this->app([
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
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$value = $dialog->value();

		$this->assertSame([
			'parent'   => 'site',
			'section'  => null,
			'slug'     => '',
			'template' => 'test',
			'title'    => '',
			'uuid'     => null,
			'view'     => null,
			'foo'      => 'bar'
		], $value);
	}

	public function testModel(): void
	{
		$this->app([
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
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$model = $dialog->model();

		$this->assertInstanceOf(Page::class, $model);
		$this->assertInstanceOf(MemoryStorage::class, $model->storage());
		$this->assertInstanceOf(PageUuid::class, $model->uuid());
	}

	public function testModelSlugFallsBackToNewWhenNull(): void
	{
		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null,
			null // slug = null
		);

		$this->assertSame('__new__', $dialog->model()->slug());
	}

	public function testModelSlugFallsBackToNewWhenEmpty(): void
	{
		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null,
			'' // slug = empty string
		);

		$this->assertSame('__new__', $dialog->model()->slug());
	}

	public function testModelSlugUsesProvidedSlug(): void
	{
		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null,
			'my-article' // slug = valid slug
		);

		$this->assertSame('my-article', $dialog->model()->slug());
	}

	public function testModelUuidDisabled(): void
	{
		$this->app([
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
			]
		]);

		$this->login();

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$model = $dialog->model();

		$this->assertInstanceOf(Page::class, $model);
		$this->assertInstanceOf(MemoryStorage::class, $model->storage());
		$this->assertNull($model->uuid());
	}

	public function testResolveTitleSlugFields(): void
	{
		$app = $this->app([
			'blueprints' => [
				'pages/test' => [
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
			]
		]);
		$app->impersonate('kirby');

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null,
			'bar-slug',
			'Foo title'
		);

		$dialog->submit([]);
		$page = $app->page('bar-slug');

		$this->assertSame('Foo title', $page->title()->value());
		$this->assertSame('Foo title', $page->foo()->value());
		$this->assertSame('bar-slug', $page->slug());
		$this->assertSame('bar-slug', $page->bar()->value());
	}

	public function testSubmitDefault(): void
	{
		$app = $this->app([
			'blueprints' => [
				'pages/article' => [
					'fields' => [
						'text' => ['type' => 'text']
					]
				]
			]
		]);
		$app->impersonate('kirby');

		$dialog = new PageCreateDialog(
			null,
			null,
			'article',
			null,
			'my-article',
			'My Article'
		);

		$dialog->submit([]);
		$page = $app->page('my-article');

		$this->assertSame('my-article', $page->slug());
		$this->assertSame('My Article', $page->title()->value());
		$this->assertSame('', $page->text()->value());
	}

	public function testSubmitWithSlugFromTemplate(): void
	{
		// slug field is hidden when create.slug is set; Panel submits slug: ''
		$app = $this->app([
			'blueprints' => [
				'pages/article' => [
					'create' => [
						'slug' => 'art-{{ page.category }}'
					],
					'fields' => [
						'category' => ['type' => 'text']
					]
				]
			]
		]);
		$app->impersonate('kirby');

		$dialog = new PageCreateDialog(
			null,
			null,
			'article',
			null,
			'',          // empty string – simulates hidden slug field
			'My Article'
		);

		$dialog->submit(['category' => 'photo']);
		$page = $app->page('art-photo');

		$this->assertSame('art-photo', $page->slug());
		$this->assertSame('My Article', $page->title()->value());
	}

	public function testSubmitWithTitleAndSlugFromTemplate(): void
	{
		$app = $this->app([
			'blueprints' => [
				'pages/article' => [
					'create' => [
						'title'  => 'New: {{ page.category }}',
						'slug'   => 'cat-{{ page.category }}',
						'fields' => ['category']
					],
					'fields' => [
						'category' => ['type' => 'text']
					]
				]
			]
		]);
		$app->impersonate('kirby');

		$dialog = new PageCreateDialog(
			null,
			null,
			'article',
			null
		);

		$dialog->submit(['category' => 'photo']);
		$page = $app->page('cat-photo');

		$this->assertSame('cat-photo', $page->slug());
		$this->assertSame('New: photo', $page->title()->value());
		$this->assertSame('photo', $page->category()->value());
	}

	public function testSubmitDoesNotLeakParentContent(): void
	{
		$app = $this->app([
			'blueprints' => [
				'pages/parent-page' => [
					'fields' => [
						'summary' => ['type' => 'text']
					]
				],
				'pages/article' => [
					'create' => [
						'slug' => 'child-{{ page.category }}'
					],
					'fields' => [
						'category' => ['type' => 'text']
					]
				]
			]
		]);
		$app->impersonate('kirby');

		Page::create([
			'slug'     => 'parent',
			'template' => 'parent-page',
			'content'  => ['title' => 'Parent', 'summary' => 'Parent content']
		]);

		// parentId uses the pages/id format required by Find::parent()
		$dialog = new PageCreateDialog(
			'pages/parent',
			null,
			'article',
			null,
			'',       // empty string – simulates hidden slug field
			'Child'
		);

		$dialog->submit(['category' => 'test']);
		$child = $app->page('parent/child-test');

		$this->assertSame('child-test', $child->slug());
		$this->assertSame('Child', $child->title()->value());
		$this->assertFalse($child->summary()->exists());

		// read the content file directly to verify no parent data was written to disk
		$contentFile = $child->version('latest')->contentFile();
		$content = file_get_contents($contentFile);

		$this->assertStringNotContainsString('Summary', $content);
		$this->assertStringNotContainsString('Parent content', $content);
	}
}
