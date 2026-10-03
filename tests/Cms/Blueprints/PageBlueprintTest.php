<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PageBlueprint::class)]
class PageBlueprintTest extends TestCase
{
	protected function tearDown(): void
	{
		Blueprint::$loaded = [];
	}

	public function testOptions(): void
	{
		$blueprint = new PageBlueprint([
			'model' => new Page(['slug' => 'test'])
		]);

		$expected = [
			'access'     	 => null,
			'changeSlug'     => null,
			'changeStatus'   => null,
			'changeTemplate' => null,
			'changeTitle'    => null,
			'create'         => null,
			'delete'         => null,
			'duplicate'      => null,
			'list'			 => null,
			'move'           => null,
			'preview'        => null,
			'read'           => null,
			'preview'        => null,
			'sort'           => null,
			'update'         => null,
			'move'			 => null
		];

		$this->assertEquals($expected, $blueprint->options()); // cannot use strict assertion (array order)
	}

	public function testExtendedOptionsFromString(): void
	{
		new App([
			'blueprints' => [
				'options/default' => [
					'changeSlug'     => true,
					'changeTemplate' => false,
				]
			]
		]);

		$blueprint = new PageBlueprint([
			'model'   => new Page(['slug' => 'test']),
			'options' => 'options/default'
		]);

		$expected = [
			'access'     	 => null,
			'changeSlug'     => true,
			'changeStatus'   => null,
			'changeTemplate' => false,
			'changeTitle'    => null,
			'create'         => null,
			'delete'         => null,
			'duplicate'      => null,
			'list'					 => null,
			'move'           => null,
			'preview'        => null,
			'read'           => null,
			'preview'        => null,
			'sort'           => null,
			'update'         => null,
			'move'			 => null
		];

		$this->assertEquals($expected, $blueprint->options()); // cannot use strict assertion (array order)
	}

	public function testExtendedOptions(): void
	{
		new App([
			'blueprints' => [
				'options/default' => [
					'changeSlug' => true,
					'changeTemplate' => false,
				]
			]
		]);

		$blueprint = new PageBlueprint([
			'model'   => new Page(['slug' => 'test']),
			'options' => [
				'extends' => 'options/default',
				'create'  => false
			]
		]);

		$expected = [
			'access'     	 => null,
			'changeSlug'     => true,
			'changeStatus'   => null,
			'changeTemplate' => false,
			'changeTitle'    => null,
			'create'         => false,
			'delete'         => null,
			'duplicate'      => null,
			'list'					 => null,
			'move'           => null,
			'preview'        => null,
			'read'           => null,
			'preview'        => null,
			'sort'           => null,
			'update'         => null,
			'move'			 => null
		];

		$this->assertEquals($expected, $blueprint->options()); // cannot use strict assertion (array order)
	}

	public static function numProvider(): array
	{
		return [
			['default', 'default'],
			['sort', 'default'],
			['zero', 'zero'],
			[0, 'zero'],
			['0', 'zero'],
			['date', 'date'],
			['datetime', 'datetime'],
			['{{ page.something }}', '{{ page.something }}'],
		];
	}

	#[DataProvider('numProvider')]
	public function testNum($input, $expected): void
	{
		$blueprint = new PageBlueprint([
			'model' => new Page(['slug' => 'test']),
			'num'   => $input
		]);

		$this->assertSame($expected, $blueprint->num());
	}

	public function testStatusWithCustomIconAndTheme(): void
	{
		$blueprint = new PageBlueprint([
			'model'  => new Page(['slug' => 'test']),
			'status' => [
				'draft'    => ['label' => 'Idea', 'icon' => 'edit', 'theme' => 'notice'],
				'unlisted' => ['label' => 'Review', 'icon' => '👀'],
				'listed'   => 'Live'
			]
		]);

		$status = $blueprint->status();

		$this->assertSame('edit', $status['draft']['icon']);
		$this->assertSame('notice', $status['draft']['theme']);
		$this->assertSame('👀', $status['unlisted']['icon']);
		$this->assertSame('info', $status['unlisted']['theme']);
		$this->assertSame('status-listed', $status['listed']['icon']);
		$this->assertSame('positive', $status['listed']['theme']);
	}

	public function testStatus(): void
	{
		$blueprint = new PageBlueprint([
			'model'  => new Page(['slug' => 'test']),
			'status' => [
				'draft'    => 'Draft Label',
				'unlisted' => 'Unlisted Label',
				'listed'   => 'Listed Label'
			]
		]);

		$expected = [
			'draft' => [
				'label' => 'Draft Label',
				'text'  => null,
				'icon'  => 'status-draft',
				'theme' => 'negative'
			],
			'unlisted' => [
				'label' => 'Unlisted Label',
				'text'  => null,
				'icon'  => 'status-unlisted',
				'theme' => 'info'
			],
			'listed' => [
				'label' => 'Listed Label',
				'text'  => null,
				'icon'  => 'status-listed',
				'theme' => 'positive'
			]
		];

		$this->assertSame($expected, $blueprint->status());
	}

	public function testStatusWithCustomText(): void
	{
		$expected = [
			'draft' => [
				'label' => 'Draft Label',
				'text'  => 'Draft Text',
				'icon'  => 'status-draft',
				'theme' => 'negative'
			],
			'unlisted' => [
				'label' => 'Unlisted Label',
				'text'  => 'Unlisted Text',
				'icon'  => 'status-unlisted',
				'theme' => 'info'
			],
			'listed' => [
				'label' => 'Listed Label',
				'text'  => 'Listed Text',
				'icon'  => 'status-listed',
				'theme' => 'positive'
			]
		];

		$blueprint = new PageBlueprint([
			'model'  => new Page(['slug' => 'test']),
			'status' => $expected,
		]);

		$this->assertSame($expected, $blueprint->status());
	}

	public function testStatusTranslations(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$input = [
			'draft' => [
				'label' => ['en' => 'Draft Label'],
				'text'  => ['en' => 'Draft Text']
			],
			'unlisted' => [
				'label' => ['en' => 'Unlisted Label'],
				'text'  => ['en' => 'Unlisted Text']
			],
			'listed' => [
				'label' => ['en' => 'Listed Label'],
				'text'  => ['en' => 'Listed Text']
			]
		];

		$expected = [
			'draft' => [
				'label' => 'Draft Label',
				'text'  => 'Draft Text',
				'icon'  => 'status-draft',
				'theme' => 'negative'
			],
			'unlisted' => [
				'label' => 'Unlisted Label',
				'text'  => 'Unlisted Text',
				'icon'  => 'status-unlisted',
				'theme' => 'info'
			],
			'listed' => [
				'label' => 'Listed Label',
				'text'  => 'Listed Text',
				'icon'  => 'status-listed',
				'theme' => 'positive'
			]
		];

		$blueprint = new PageBlueprint([
			'model'  => new Page(['slug' => 'test']),
			'status' => $input,
		]);

		$this->assertSame($expected, $blueprint->status());
	}

	public function testInvalidStatus(): void
	{
		$input = [
			'draft'    => 'Draft',
			'unlisted' => 'Unlisted',
			'foo'      => 'Bar'
		];

		$expected = [
			'draft' => [
				'label' => 'Draft',
				'text'  => null,
				'icon'  => 'status-draft',
				'theme' => 'negative'
			],
			'unlisted' => [
				'label' => 'Unlisted',
				'text'  => null,
				'icon'  => 'status-unlisted',
				'theme' => 'info'
			],
		];

		$blueprint = new PageBlueprint([
			'model'  => new Page(['slug' => 'test']),
			'status' => $input,
		]);

		$this->assertSame($expected, $blueprint->status());
	}

	public function testExtendStatus(): void
	{
		new App([
			'blueprints' => [
				'status/default' => [
					'draft'    => [
						'label' => 'Draft Label',
						'text'  => null,
					],
					'unlisted' => [
						'label' => 'Unlisted Label',
						'text'  => null,
					],
					'listed' => [
						'label' => 'Listed Label',
						'text'  => null
					]
				],
			]
		]);

		$input = [
			'extends'  => 'status/default',
			'draft'    => [
				'label' => 'Draft',
				'text'  => null,
			],
			'unlisted' => false,
			'listed' => [
				'label' => 'Published',
				'text'  => null
			]
		];

		$expected = [
			'draft' => [
				'label' => 'Draft',
				'text'  => null,
				'icon'  => 'status-draft',
				'theme' => 'negative'
			],
			'listed' => [
				'label' => 'Published',
				'text'  => null,
				'icon'  => 'status-listed',
				'theme' => 'positive'
			],
		];

		$blueprint = new PageBlueprint([
			'model' => new Page(['slug' => 'test']),
			'status' => $input
		]);

		$this->assertSame($expected, $blueprint->status());
	}

	public function testExtendStatusFromString(): void
	{
		new App([
			'blueprints' => [
				'status/default' => $expected = [
					'draft'    => [
						'label' => 'Draft Label',
						'text'  => null,
						'icon'  => 'status-draft',
						'theme' => 'negative',
					],
					'unlisted' => [
						'label' => 'Unlisted Label',
						'text'  => null,
						'icon'  => 'status-unlisted',
						'theme' => 'info',
					],
					'listed' => [
						'label' => 'Listed Label',
						'text'  => null,
						'icon'  => 'status-listed',
						'theme' => 'positive'
					]
				],
			]
		]);

		$blueprint = new PageBlueprint([
			'model' => new Page(['slug' => 'test']),
			'status' => 'status/default'
		]);

		$this->assertSame($expected, $blueprint->status());
	}

	public function testExtendNum(): void
	{
		new App([
			'blueprints' => [
				'pages/test' => [
					'title' => 'Extension Test',
					'num' => 'date'
				]
			]
		]);

		$blueprint = new PageBlueprint([
			'extends' => 'pages/test',
			'title' => 'Extended',
			'model'   => new Page(['slug' => 'test'])
		]);

		$this->assertSame('Extended', $blueprint->title());
		$this->assertSame('date', $blueprint->num());
	}

	public function testTitleI18n(): void
	{
		$app = new App([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test'
					]
				]
			],
			'blueprints' => [
				'pages/test' => [
					'name'  => 'Foo',
					'title' => 'page.test'
				]
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true,
					'translations' => [
						'page.test' => 'Simple Page'
					]
				],
				[
					'code' => 'de',
					'translations' => [
						'page.test' => 'Einfache Seite'
					],
				]
			]
		]);

		$app->setCurrentTranslation('de');
		$page = $app->page('test');

		$this->assertSame('Einfache Seite', $page->blueprint()->title());

		$app->setCurrentTranslation('en');
		$page->purge();

		$this->assertSame('Simple Page', $page->blueprint()->title());
	}

	public function testTitleI18nWithFallbackLanguage(): void
	{
		$app = new App([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test-fallback'
					]
				]
			],
			'blueprints' => [
				'pages/test-fallback' => [
					'name'  => 'Foo',
					'title' => 'page.test'
				]
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true,
					'translations' => [
						'page.test' => 'Thanks to fallback'
					]
				],
				[
					'code' => 'de',
					'translations' => [],
				]
			]
		]);

		$app->setCurrentTranslation('de');
		$page = $app->page('test');
		$this->assertSame('Thanks to fallback', $page->blueprint()->title());
	}

	public function testTitleI18nArray(): void
	{
		$app = new App([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test-i18n-array'
					]
				]
			],
			'blueprints' => [
				'pages/test-i18n-array' => [
					'name'  => 'Foo',
					'title' => [
						'en' => 'My title',
						'de' => 'Mein Titel'
					]
				]
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			]
		]);

		$app->setCurrentTranslation('de');
		$page = $app->page('test');
		$this->assertSame('Mein Titel', $page->blueprint()->title());

		$page->purge();
		$app->setCurrentTranslation('en');
		$this->assertSame('My title', $page->blueprint()->title());
	}

	public function testTitleI18nArrayFallBack(): void
	{
		$app = new App([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test-i18n-array'
					]
				]
			],
			'blueprints' => [
				'pages/test-i18n-array' => [
					'name'  => 'Foo',
					'title' => [
						'en' => 'My title',
						'de' => 'Mein Titel'
					]
				]
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			]
		]);

		$app->setCurrentTranslation('fr');
		$page = $app->page('test');
		$this->assertSame('My title', $page->blueprint()->title());
	}
}
