<?php

namespace Kirby\Panel\Areas;

class PageDialogsTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	public function testCreate(): void
	{
		$dialog = $this->dialog('pages/create');
		$props  = $dialog['props'];

		$this->assertSame('k-page-create-dialog', $dialog['component']);

		$this->assertSame('Title', $props['fields']['title']['label']);
		$this->assertSame('URL appendix', $props['fields']['slug']['label']);
		$this->assertSame('title', $props['fields']['slug']['sync']);
		$this->assertTrue($props['fields']['parent']['hidden']);

		// there's only the default template for now
		$this->assertTrue($props['fields']['template']['hidden']);

		$this->assertSame('Create as Draft', $props['submitButton']);

		$this->assertSame('site', $props['value']['parent']);
		$this->assertSame('', $props['value']['slug']);
		$this->assertSame('default', $props['value']['template']);
		$this->assertSame('', $props['value']['title']);
	}

	public function testCreateWithParent(): void
	{
		$this->app([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'request' => [
				'query' => [
					'parent' => 'pages/test'
				]
			]
		]);

		$this->login();

		$dialog = $this->dialog('pages/create');
		$props  = $dialog['props'];

		$this->assertSame('pages/test', $props['value']['parent']);
	}

	public function testCreateWithMultipleBlueprints(): void
	{
		$this->app([
			'blueprints' => [
				'pages/a' => ['title' => 'A'],
				'pages/b' => ['title' => 'B'],
			]
		]);

		$this->login();

		$dialog = $this->dialog('pages/create');
		$props  = $dialog['props'];

		// a + b + default
		$this->assertCount(3, $props['blueprints']);
	}

	public function testCreateWithCustomTitleLabel(): void
	{
		$this->app([
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

		$this->login();

		$dialog = $this->dialog('pages/create');
		$this->assertSame($label, $dialog['props']['fields']['title']['label']);
	}

	public function testCreateWithI18nTitleLabel(): void
	{
		$this->app([
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

		$this->login();

		$dialog = $this->dialog('pages/create');
		$this->assertSame($label, $dialog['props']['fields']['title']['label']);
	}

	public function testCreateWithCustomField(): void
	{
		$this->app([
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

		$this->login();

		$dialog = $this->dialog('pages/create');
		$props  = $dialog['props'];

		$this->assertArrayHasKey('foo', $props['fields']);
		$this->assertSame('1/1', $props['fields']['foo']['width']);
	}

	public function testCreateWithUnknownCustomField(): void
	{
		$this->app([
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

		$this->login();

		$dialog = $this->dialog('pages/create');
		$this->assertSame('Unknown field  "notthere" in create dialog', $dialog['error']);
	}

	public function testCreateWithUnsupportedCustomField(): void
	{
		$this->app([
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

		$this->login();

		$dialog = $this->dialog('pages/create');
		$this->assertSame('Field type "files" not supported in create dialog', $dialog['error']);
	}

	public function testCreateWithForbiddenCustomField(): void
	{
		$this->app([
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

		$this->login();

		$dialog = $this->dialog('pages/create');
		$this->assertSame('Field name "slug" not allowed as custom field in create dialog', $dialog['error']);
	}

	public function testCreateOnSubmit(): void
	{
		$this->submit([
			'title' => 'Test',
			'slug'  => 'test'
		]);

		$dialog = $this->dialog('pages/create');

		$this->assertSame('page.create', $dialog['event']);
		$this->assertSame(200, $dialog['code']);

		$this->assertSame('test', $this->app->page('test')->slug());
		$this->assertSame('Test', $this->app->page('test')->title()->value());
		$this->assertSame('draft', $this->app->page('test')->status());
	}

	public function testCreateOnSubmitWithParent(): void
	{
		$this->app([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'request' => [
				'query' => [
					'parent' => 'pages/test'
				]
			]
		]);

		$this->submit([
			'title' => 'Test',
			'slug'  => 'test-child'
		]);

		$dialog = $this->dialog('pages/create');

		$this->assertSame('test-child', $this->app->page('test/test-child')->slug());
		$this->assertSame('Test', $this->app->page('test/test-child')->title()->value());
	}

	public function testCreateOnSubmitWithCustomField(): void
	{
		$this->app([
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
			]
		]);

		$this->submit([
			'title' => 'Test',
			'slug'  => 'test',
			'foo'   => 'bar',
			'homer' => 'simpson'
		]);

		$this->dialog('pages/create');

		$this->assertSame('bar', $this->app->page('test')->foo()->value());
		$this->assertNull($this->app->page('test')->homer()->value());
	}

	public function testCreateOnSubmitWithoutTitle(): void
	{
		$this->submit([
			'slug' => 'test'
		]);

		$dialog = $this->dialog('pages/create');

		$this->assertSame(400, $dialog['code']);
		$this->assertSame('The title must not be empty', $dialog['error']);
	}

	public function testCreateOnSubmitWithCustomStatus(): void
	{
		$this->app([
			'blueprints' => [
				'pages/default' => [
					'create' => [
						'status' => 'unlisted'
					]
				]
			]
		]);

		$this->submit([
			'title' => 'Test',
			'slug'  => 'test'
		]);

		$this->dialog('pages/create');
		$this->assertSame('unlisted', $this->app->page('test')->status());
	}
}
