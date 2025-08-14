<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Panel\Model;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PagesSectionTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PagesSection';

	public function setUp(): void
	{
		Dir::make(static::TMP);

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
		App::destroy();
	}

	public function testBatchDefault(): void
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertFalse($section->batch());
		$this->assertFalse($section->toArray()['options']['batch']);
	}

	public function testBatchDisabled(): void
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'batch' => false
		]);

		$this->assertFalse($section->batch());
		$this->assertFalse($section->toArray()['options']['batch']);
	}

	public function testBatchEnabled(): void
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'batch' => true
		]);

		$this->assertTrue($section->batch());
		$this->assertTrue($section->toArray()['options']['batch']);
	}

	public function testHeadline(): void
	{
		// single headline
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'label' => 'Test'
		]);

		$this->assertSame('Test', $section->headline());

		// translated headline
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'label' => [
				'en' => 'Pages',
				'de' => 'Seiten'
			]
		]);

		$this->assertSame('Pages', $section->headline());
	}

	public function testHeadlineFromLabel(): void
	{
		// single label
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'label' => 'Test'
		]);

		$this->assertSame('Test', $section->headline());

		// translated label
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'label' => [
				'en' => 'Pages',
				'de' => 'Seiten'
			]
		]);

		$this->assertSame('Pages', $section->headline());
	}


	public function testParent(): void
	{
		$parent = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a']
			]
		]);

		// regular parent
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => $parent,
		]);

		$this->assertSame('test', $section->parent()->id());

		// page.find
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => $parent,
			'parent' => 'page.find("a")'
		]);

		$this->assertSame('test/a', $section->parent()->id());
	}

	public function testParentWithInvalidOption(): void
	{
		$parent = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a']
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The parent is invalid. You must choose the site or a page as parent.');

		new Section('pages', [
			'name'  => 'test',
			'model' => $parent,
			'parent' => 'kirby.user'
		]);
	}

	public static function statusProvider(): array
	{
		return [
			[null, 'all'],
			['', 'all'],
			['draft', 'draft'],
			['drafts', 'draft'],
			['published', 'published'],
			['listed', 'listed'],
			['unlisted', 'unlisted'],
			['invalid', 'all'],
		];
	}

	#[DataProvider('statusProvider')]
	public function testStatus($input, $expected): void
	{
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => new Page(['slug' => 'test']),
			'status' => $input
		]);

		$this->assertSame($expected, $section->status());
	}

	public function testAdd(): void
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertTrue($section->add());
	}

	public static function addableStatusProvider(): array
	{
		return [
			['all', true],
			['draft', true],
			['published', false],
			['listed', false],
			['unlisted', false],
		];
	}

	#[DataProvider('addableStatusProvider')]
	public function testAddWhenStatusIs($input, $expected): void
	{
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => new Page(['slug' => 'test']),
			'status' => $input
		]);

		$this->assertSame($expected, $section->add());
	}

	public static function addableStatusCreateProvider(): array
	{
		return [
			['draft', 'all', true],
			['draft', 'draft', true],
			['draft', 'unlisted', false],
			['draft', 'listed', false],
			['unlisted', 'all', true],
			['unlisted', 'draft', false],
			['unlisted', 'unlisted', true],
			['unlisted', 'listed', false],
			['listed', 'all', true],
			['listed', 'draft', false],
			['listed', 'unlisted', false],
			['listed', 'listed', true],
		];
	}

	#[DataProvider('addableStatusCreateProvider')]
	public function testAddWhenStatusCreatedMatchesStatusShown($create, $shown, $expected): void
	{
		$this->app->clone([
			'blueprints' => [
				'pages/child' => [
					'create' => ['status' => $create]
				]
			]
		]);

		$section = new Section('pages', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'status'   => $shown,
			'template' => 'child'
		]);

		$this->assertSame($expected, $section->add());
		Blueprint::$loaded = [];
	}

	public function testAddWhenMultipleStatusCreated(): void
	{
		$this->app->clone([
			'blueprints' => [
				'pages/child-a' => [
					'create' => ['status' => 'listed']
				],
				'pages/child-b' => [
					'create' => ['status' => 'unlisted']
				]
			]
		]);

		$section = new Section('pages', [
			'name'      => 'test',
			'model'     => new Page(['slug' => 'test']),
			'status'    => 'listed',
			'templates' => ['child-a', 'child-b']
		]);

		$this->assertFalse($section->add());
	}

	public function testAddWhenSectionIsFull(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage']
			]
		]);

		$section = new Section('pages', [
			'name'  => 'test',
			'model' => $page,
			'max'   => 1
		]);

		$this->assertFalse($section->add());
	}

	public function testSortBy(): void
	{
		$locale = setlocale(LC_ALL, 0);
		setlocale(LC_ALL, ['de_DE.ISO8859-1', 'de_DE']);

		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'Z']],
				['slug' => 'subpage-2', 'content' => ['title' => 'Ä']],
				['slug' => 'subpage-3', 'content' => ['title' => 'B']]
			]
		]);

		// no settings
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => $page
		]);
		$this->assertSame('Z', $section->data()[0]['text']);
		$this->assertSame('Ä', $section->data()[1]['text']);
		$this->assertSame('B', $section->data()[2]['text']);

		// sort by field
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $page,
			'sortBy' => 'title'
		]);
		$this->assertSame('B', $section->data()[0]['text']);
		$this->assertSame('Z', $section->data()[1]['text']);
		$this->assertSame('Ä', $section->data()[2]['text']);

		// custom sorting direction
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $page,
			'sortBy' => 'title desc'
		]);
		$this->assertSame('Ä', $section->data()[0]['text']);
		$this->assertSame('Z', $section->data()[1]['text']);
		$this->assertSame('B', $section->data()[2]['text']);

		// custom flag
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $page,
			'sortBy' => 'title SORT_LOCALE_STRING'
		]);
		$this->assertSame('Ä', $section->data()[0]['text']);
		$this->assertSame('B', $section->data()[1]['text']);
		$this->assertSame('Z', $section->data()[2]['text']);

		// flag & sorting direction
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $page,
			'sortBy' => 'title desc SORT_LOCALE_STRING'
		]);
		$this->assertSame('Z', $section->data()[0]['text']);
		$this->assertSame('B', $section->data()[1]['text']);
		$this->assertSame('Ä', $section->data()[2]['text']);

		setlocale(LC_ALL, $locale);
	}

	public function testSortByMultiple(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-3', 'content' => ['title' => 'B']],
				['slug' => 'subpage-4', 'content' => ['title' => 'A']],
				['slug' => 'subpage-1', 'content' => ['title' => 'A']],
				['slug' => 'subpage-2', 'content' => ['title' => 'B']]
			]
		]);

		// simple multiple fields
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $page,
			'sortBy' => 'title asc slug desc'
		]);

		$this->assertSame('test/subpage-4', $section->data()[0]['id']);
		$this->assertSame('test/subpage-1', $section->data()[1]['id']);
		$this->assertSame('test/subpage-3', $section->data()[2]['id']);
		$this->assertSame('test/subpage-2', $section->data()[3]['id']);

		// multiple fields with comma
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $page,
			'sortBy' => 'title desc, slug asc'
		]);

		$this->assertSame('test/subpage-2', $section->data()[0]['id']);
		$this->assertSame('test/subpage-3', $section->data()[1]['id']);
		$this->assertSame('test/subpage-1', $section->data()[2]['id']);
		$this->assertSame('test/subpage-4', $section->data()[3]['id']);
	}

	public function testSortable(): void
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertTrue($section->sortable());
	}

	public function testDisableSortable(): void
	{
		$section = new Section('pages', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'sortable' => false
		]);

		$this->assertFalse($section->sortable());
	}

	public function testFlip(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'C']],
				['slug' => 'subpage-2', 'content' => ['title' => 'A']],
				['slug' => 'subpage-3', 'content' => ['title' => 'B']]
			]
		]);

		$section = new Section('pages', [
			'name'  => 'test',
			'model' => $page,
			'flip'  => true
		]);

		$this->assertSame('B', $section->data()[0]['text']);
		$this->assertSame('A', $section->data()[1]['text']);
		$this->assertSame('C', $section->data()[2]['text']);
	}

	public static function sortableStatusProvider(): array
	{
		return [
			['all', true],
			['listed', true],
			['published', true],
			['draft', false],
			['unlisted', false],
		];
	}

	#[DataProvider('sortableStatusProvider')]
	public function testSortableStatus($input, $expected): void
	{
		$section = new Section('pages', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'status'   => $input
		]);

		$this->assertSame($expected, $section->sortable());
	}

	public function testImageString(): void
	{
		$model = new Page([
			'slug' => 'test',
			'children' => [
				[
					'slug' => 'a',
					'files' => [
						['filename' => 'cover.jpg']
					]
				],
				[
					'slug' => 'b',
					'files' => [
						['filename' => 'cover.jpg']
					]
				],
				[
					'slug' => 'c'
				]
			]
		]);

		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model,
			'image'  => 'page.image("cover.jpg")',
		]);

		$data = $section->data();

		// existing covers
		$this->assertStringContainsString(Model::imagePlaceholder(), $data[0]['image']['src']);

		// non-existing covers
		$this->assertArrayNotHasKey('src', $data[2]['image']);
	}

	public function testTemplates(): void
	{
		// single template
		$section = new Section('pages', [
			'name'      => 'test',
			'model'     => new Page(['slug' => 'test']),
			'templates' => 'blog'
		]);

		$this->assertSame(['blog'], $section->templates());

		// multiple templates
		$section = new Section('pages', [
			'name'      => 'test',
			'model'     => new Page(['slug' => 'test']),
			'templates' => ['blog', 'notes']
		]);

		$this->assertSame(['blog', 'notes'], $section->templates());

		// template via alias
		$section = new Section('pages', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'template' => 'blog'
		]);

		$this->assertSame(['blog'], $section->templates());
	}

	public function testEmpty(): void
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $section->empty());
	}

	public function testTranslatedEmpty(): void
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);

		$this->assertSame('Test', $section->empty());
	}

	public function testHelp(): void
	{
		// single help
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'help'  => 'Test'
		]);

		$this->assertSame('<p>Test</p>', $section->help());

		// translated help
		$section = new Section('pages', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'help' => [
				'en' => 'Information',
				'de' => 'Informationen'
			]
		]);

		$this->assertSame('<p>Information</p>', $section->help());
	}

	public function testTranslatedInfo(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'C']],
				['slug' => 'subpage-2', 'content' => ['title' => 'A']],
				['slug' => 'subpage-3', 'content' => ['title' => 'B']]
			]
		]);

		$section = new Section('pages', [
			'name'  => 'test',
			'model' => $page,
			'info' => [
				'en' => 'en: {{ page.slug }}',
				'de' => 'de: {{ page.slug }}'
			]
		]);

		$this->assertSame('en: {{ page.slug }}', $section->info());
		$this->assertSame('en: subpage-1', $section->data()[0]['info']);
		$this->assertSame('en: subpage-2', $section->data()[1]['info']);
		$this->assertSame('en: subpage-3', $section->data()[2]['info']);
	}

	public function testTranslatedText(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'C']],
				['slug' => 'subpage-2', 'content' => ['title' => 'A']],
				['slug' => 'subpage-3', 'content' => ['title' => 'B']]
			]
		]);

		$section = new Section('pages', [
			'name'  => 'test',
			'model' => $page,
			'text' => [
				'en' => 'en: {{ page.title }}',
				'de' => 'de: {{ page.title }}'
			]
		]);

		$this->assertSame('en: {{ page.title }}', $section->text());
		$this->assertSame('en: C', $section->data()[0]['text']);
		$this->assertSame('en: A', $section->data()[1]['text']);
		$this->assertSame('en: B', $section->data()[2]['text']);
	}

	public function testUnreadable(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'pages/unreadable' => [
					'options' => ['read' => false]
				]
			],
			'users' => [
				['id' => 'bastian', 'role' => 'admin']
			]
		]);

		$app->impersonate('bastian');

		$page = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'subpage-a'],
				['slug' => 'subpage-b', 'template' => 'unreadable'],
				['slug' => 'subpage-c']
			]
		]);

		$section = new Section('pages', [
			'name'  => 'test',
			'model' => $page
		]);

		$this->assertCount(2, $section->data());
	}

	public function testSearchDefault(): void
	{
		$model = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'Mount Bike']],
				['slug' => 'subpage-2', 'content' => ['title' => 'Mountain']],
				['slug' => 'subpage-3', 'content' => ['title' => 'Bike']]
			]
		]);

		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model
		]);

		$this->assertCount(3, $section->data());
	}

	public function testSearchWithNoQuery(): void
	{
		$model = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'Mount Bike']],
				['slug' => 'subpage-2', 'content' => ['title' => 'Mountain']],
				['slug' => 'subpage-3', 'content' => ['title' => 'Bike']]
			]
		]);

		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true
		]);

		$this->assertCount(3, $section->data());
	}

	public function testSearchWithQuery1(): void
	{
		$_GET['searchterm'] = 'bike';

		$model = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'Mount Bike']],
				['slug' => 'subpage-2', 'content' => ['title' => 'Mountain']],
				['slug' => 'subpage-3', 'content' => ['title' => 'Bike']]
			]
		]);

		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true
		]);

		$this->assertCount(2, $section->data());
		$this->assertSame('Bike', $section->data()[0]['text']);
		$this->assertSame('Mount Bike', $section->data()[1]['text']);

		$_GET = [];
	}

	public function testSearchWithQuery2(): void
	{
		$_GET['searchterm'] = 'mount';

		$model = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'Mount Bike']],
				['slug' => 'subpage-2', 'content' => ['title' => 'Mountain']],
				['slug' => 'subpage-3', 'content' => ['title' => 'Bike']]
			]
		]);

		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true
		]);

		$this->assertCount(2, $section->data());
		$this->assertSame('Mount Bike', $section->data()[0]['text']);
		$this->assertSame('Mountain', $section->data()[1]['text']);

		$_GET = [];
	}

	public function testSearchWithQuery3(): void
	{
		$_GET['searchterm'] = 'mountain';

		$model = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'Mount Bike']],
				['slug' => 'subpage-2', 'content' => ['title' => 'Mountain']],
				['slug' => 'subpage-3', 'content' => ['title' => 'Bike']]
			]
		]);

		$_GET['searchterm'] = 'mountain';
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true
		]);

		$this->assertCount(1, $section->data());
		$this->assertSame('Mountain', $section->data()[0]['text']);

		$_GET = [];
	}

	public function testSearchWithFlip(): void
	{
		$_GET['searchterm'] = 'bike';

		$model = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'Bike']],
				['slug' => 'subpage-2', 'content' => ['title' => 'Mountain']],
				['slug' => 'subpage-3', 'content' => ['title' => 'Mount Bike']]
			]
		]);

		$section = new Section('pages', [
			'flip'   => true,
			'name'   => 'test',
			'model'  => $model,
			'search' => true
		]);

		$this->assertCount(2, $section->data());
		$this->assertSame('Bike', $section->data()[0]['text']);
		$this->assertSame('Mount Bike', $section->data()[1]['text']);

		$_GET = [];
	}

	public function testSearchWithSortBy(): void
	{
		$_GET['searchterm'] = 'bike';

		$model = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'subpage-1', 'content' => ['title' => 'Bike']],
				['slug' => 'subpage-2', 'content' => ['title' => 'Mountain']],
				['slug' => 'subpage-3', 'content' => ['title' => 'Mount Bike']]
			]
		]);

		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true,
			'sortBy' => 'title desc'
		]);

		$this->assertCount(2, $section->data());
		$this->assertSame('Bike', $section->data()[0]['text']);
		$this->assertSame('Mount Bike', $section->data()[1]['text']);

		$_GET = [];
	}

	public function testTableLayout(): void
	{
		$model = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'test'],
			]
		]);

		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model,
			'layout' => 'table'
		]);

		$this->assertSame('table', $section->layout());

		$data = $section->data();
		$item = $data[0];

		$this->assertSame('', $item['info']);
		$this->assertSame([
			'text' => 'test',
			'href' => '/pages/test+test'
		], $item['title']);
	}

	public function testTableLayoutWithCustomColumns(): void
	{
		$model = new Page([
			'slug' => 'test',
			'children' => [
				[
					'slug' => 'test',
					'content' => [
						'date' => '2012-12-12'
					]
				],
			]
		]);

		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model,
			'layout' => 'table',
			'columns' => [
				'date' => [
					'label' => 'Date',
					'type'  => 'date'
				]
			]
		]);

		$this->assertSame('2012-12-12', $section->data()[0]['date']);
	}

	public function testOptions(): void
	{
		$model = new Page([
			'slug' => 'test',
			'children' => [
				[
					'slug' => 'test',
					'content' => [
						'date' => '2012-12-12'
					]
				],
			]
		]);

		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => $model
		]);

		$options = $section->toArray()['options'];

		$this->assertSame([], $options['columns']);
		$this->assertNull($options['link']);
	}

	public function testQuery(): void
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page([
				'slug' => 'test',
				'children' => [
					[
						'slug' => 'a',
						'num'  => 1,
					],
					[
						'slug' => 'b',
						'num'  => 2,
					]
				]
			]),
			'query' => $query = 'page.children.filter("num", ">", 1)'
		]);

		$this->assertSame($query, $section->query());
		$this->assertFalse($section->sortable());

		$this->assertCount(1, $section->pages());
	}

	public function testTemplatesIgnore(): void
	{
		$parent = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a', 'template' => 'foo'],
				['slug' => 'b', 'template' => 'bar'],
				['slug' => 'c', 'template' => 'baz']
			]
		]);

		// test 1
		$section = new Section('pages', [
			'name'            => 'test',
			'model'           => $parent,
			'templatesIgnore' => $expected = [
				'foo',
				'baz'
			]
		]);

		$this->assertSame($expected, $section->templatesIgnore());
		$this->assertCount(1, $section->pages());

		// test 2
		$section = new Section('pages', [
			'name'            => 'test',
			'model'           => $parent,
			'templatesIgnore' => $expected = [
				'bar'
			]
		]);

		$this->assertSame($expected, $section->templatesIgnore());
		$this->assertCount(2, $section->pages());

		// test 3
		$section = new Section('pages', [
			'name'            => 'test',
			'model'           => $parent,
			'templatesIgnore' => $expected = [
				'not-exists'
			]
		]);

		$this->assertSame($expected, $section->templatesIgnore());
		$this->assertCount(3, $section->pages());
	}

	public function testBlueprints(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'pages/default' => ['title' => 'Default'],
				'pages/section-a' => ['title' => 'Section A'],
				'pages/section-b' => ['title' => 'Section B'],
				'pages/section-c' => ['title' => 'Section C'],
			],
		]);

		$app->impersonate('kirby');

		$parent = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'a', 'template' => 'section-a'],
				['slug' => 'b', 'template' => 'section-b'],
				['slug' => 'c', 'template' => 'section-c']
			]
		]);

		$section = new Section('pages', [
			'name'            => 'test',
			'model'           => $parent,
			'templatesIgnore' => $expected = [
				'section-a',
				'section-c'
			]
		]);

		$this->assertSame($expected, $section->templatesIgnore());
		$this->assertCount(1, $section->pages());
		$this->assertCount(2, $section->blueprints());
	}
}
