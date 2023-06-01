<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Panel\Model;
use PHPUnit\Framework\TestCase;

class PagesSectionTest extends TestCase
{
	protected $app;
	protected $tmp;

	public function setUp(): void
	{
		App::destroy();
		Dir::make($this->tmp = __DIR__ . '/tmp');

		$this->app = new App([
			'roots' => [
				'index' => $this->tmp
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		Dir::remove($this->tmp);
	}

	public function testHeadline()
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

	public function testHeadlineFromLabel()
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


	public function testParent()
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

	public function testParentWithInvalidOption()
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

	public function statusProvider()
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

	/**
	 * @dataProvider statusProvider
	 */
	public function testStatus($input, $expected)
	{
		$section = new Section('pages', [
			'name'   => 'test',
			'model'  => new Page(['slug' => 'test']),
			'status' => $input
		]);

		$this->assertSame($expected, $section->status());
	}

	public function addableStatusProvider()
	{
		return [
			['all', true],
			['draft', true],
			['published', false],
			['listed', false],
			['unlisted', false],
		];
	}

	public function testAdd()
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertTrue($section->add());
	}

	/**
	 * @dataProvider addableStatusProvider
	 */
	public function testAddWhenStatusIs($input, $expected)
	{
		$section = new Section('pages', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'status'   => $input
		]);

		$this->assertSame($expected, $section->add());
	}

	public function testAddWhenSectionIsFull()
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

	public function testSortBy()
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

	public function testSortByMultiple()
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

	public function testSortable()
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertTrue($section->sortable());
	}

	public function testDisableSortable()
	{
		$section = new Section('pages', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'sortable' => false
		]);

		$this->assertFalse($section->sortable());
	}

	public function testFlip()
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

	public function sortableStatusProvider()
	{
		return [
			['all', true],
			['listed', true],
			['published', true],
			['draft', false],
			['unlisted', false],
		];
	}

	/**
	 * @dataProvider sortableStatusProvider
	 */
	public function testSortableStatus($input, $expected)
	{
		$section = new Section('pages', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'status'   => $input
		]);

		$this->assertSame($expected, $section->sortable());
	}

	public function testImageString()
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

	public function testTemplates()
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

	public function testEmpty()
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $section->empty());
	}

	public function testTranslatedEmpty()
	{
		$section = new Section('pages', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);

		$this->assertSame('Test', $section->empty());
	}

	public function testHelp()
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

	public function testTranslatedInfo()
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

	public function testTranslatedText()
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

	public function testUnreadable()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'pages/unreadable' => [
					'options' => ['read' => false]
				]
			]
		]);
		$app->impersonate('kirby');

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

	public function testSearchDefault()
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

	public function testSearchWithNoQuery()
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

	public function testSearchWithQuery1()
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

	public function testSearchWithQuery2()
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

	public function testSearchWithQuery3()
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

	public function testSearchWithFlip()
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

	public function testSearchWithSortBy()
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

	public function testTableLayout()
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

	public function testTableLayoutWithCustomColumns()
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

		$this->assertSame('2012-12-12', $section->data()[0]['dateCell']);
	}

	public function testOptions()
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

	public function testQuery()
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

	public function testQueryCreate()
	{
		$app = $this->app->clone([
			'options' => [
				'create' => $expected = [
					'foo',
					'bar'
				]
			]
		]);

		$app->impersonate('kirby');

		$parent = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b'],
				['slug' => 'c']
			]
		]);

		$section = new Section('pages', [
			'create' => 'kirby.option("create")',
			'model'  => $parent,
			'name'   => 'test'
		]);

		$this->assertSame($expected, $section->create());
		$this->assertCount(3, $section->pages());
	}

	public function testQueryTemplate()
	{
		$app = $this->app->clone([
			'options' => [
				'template' => 'bar'
			]
		]);

		$app->impersonate('kirby');

		$parent = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a', 'template' => 'foo'],
				['slug' => 'b', 'template' => 'bar'],
				['slug' => 'c', 'template' => 'baz']
			]
		]);

		$section = new Section('pages', [
			'name'      => 'test',
			'model'     => $parent,
			'template'  => 'kirby.option("template")'
		]);

		$this->assertSame(['bar'], $section->templates());
		$this->assertCount(1, $section->pages());
	}

	public function testQueryTemplates()
	{
		$app = $this->app->clone([
			'options' => [
				'templates' => $expected = [
					'foo',
					'bar'
				]
			]
		]);

		$app->impersonate('kirby');

		$parent = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a', 'template' => 'foo'],
				['slug' => 'b', 'template' => 'bar'],
				['slug' => 'c', 'template' => 'baz']
			]
		]);

		$section = new Section('pages', [
			'name'      => 'test',
			'model'     => $parent,
			'templates' => 'kirby.option("templates")'
		]);

		$this->assertSame($expected, $section->templates());
		$this->assertCount(2, $section->pages());
	}
}
