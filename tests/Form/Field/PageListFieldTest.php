<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PageListField::class)]
#[CoversClass(ModelListField::class)]
class PageListFieldTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Form.Fields.PageListField';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'blueprints' => [
				'pages/album' => [
					'title'  => 'Album',
					'create' => ['status' => 'listed']
				],
				'pages/note' => [
					'title' => 'Note'
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'photography',
						'children' => [
							['slug' => 'trees', 'num' => 1, 'template' => 'album'],
							['slug' => 'sky', 'num' => 2, 'template' => 'album'],
							['slug' => 'ocean', 'template' => 'note']
						],
						'drafts' => [
							['slug' => 'desert', 'template' => 'album']
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	protected function tearDown(): void
	{
		unset($_GET['page'], $_GET['searchterm']);
		parent::tearDown();
		App::destroy();
	}

	protected function pagelist(array $attr = []): PageListField
	{
		return Field::factory('pagelist', [
			'model' => $this->app->page('photography'),
			'name'  => 'albums',
			...$attr
		]);
	}

	public function testType(): void
	{
		$this->assertSame('pagelist', $this->pagelist()->type());
	}

	public function testHasNoValue(): void
	{
		$field = $this->pagelist();

		$this->assertFalse($field->hasValue());
		$this->assertFalse($field->props()['saveable']);
	}

	public function testData(): void
	{
		$data = $this->pagelist()->data();

		// all statuses, including the draft
		$this->assertCount(4, $data);
		$this->assertSame('photography/trees', $data[0]['id']);
	}

	public function testTextDefault(): void
	{
		$this->assertSame('{{ model.title }}', $this->pagelist()->text());
	}

	public static function statusProvider(): array
	{
		return [
			[null, 'all'],
			['', 'all'],
			['does-not-exist', 'all'],
			['drafts', 'draft'],
			['draft', 'draft'],
			['listed', 'listed'],
			['published', 'published'],
			['unlisted', 'unlisted']
		];
	}

	#[DataProvider('statusProvider')]
	public function testStatus(string|null $status, string $expected): void
	{
		$field = $this->pagelist(['status' => $status]);
		$this->assertSame($expected, $field->status());
	}

	public function testStatusFilter(): void
	{
		$this->assertCount(2, $this->pagelist(['status' => 'listed'])->data());
		$this->assertCount(1, $this->pagelist(['status' => 'draft'])->data());
		$this->assertCount(1, $this->pagelist(['status' => 'unlisted'])->data());
		$this->assertCount(3, $this->pagelist(['status' => 'published'])->data());
	}

	public function testTemplate(): void
	{
		$this->assertNull($this->pagelist()->template());

		$field = $this->pagelist(['template' => 'album']);
		$this->assertSame('album', $field->template());

		$field = $this->pagelist(['template' => ['album', 'note']]);
		$this->assertSame(['album', 'note'], $field->template());
	}

	public function testTemplates(): void
	{
		// falls back to the single template
		$field = $this->pagelist(['template' => 'album']);
		$this->assertSame(['album'], $field->templates());
		$this->assertCount(3, $field->data());

		// an explicit list wins
		$field = $this->pagelist([
			'template'  => 'album',
			'templates' => ['note']
		]);
		$this->assertSame(['note'], $field->templates());
	}

	public function testTemplatesIgnore(): void
	{
		$field = $this->pagelist(['templatesIgnore' => 'album']);
		$this->assertSame(['album'], $field->templatesIgnore());
	}

	public function testBlueprintNames(): void
	{
		// the templates of the list
		$field = $this->pagelist(['templates' => ['album', 'note']]);
		$this->assertSame(['album', 'note'], $field->blueprintNames());

		// `create` wins over the templates
		$field = $this->pagelist([
			'create'    => 'note',
			'templates' => ['album']
		]);
		$this->assertSame(['note'], $field->blueprintNames());
	}

	public function testBlueprintNamesWithIgnore(): void
	{
		$field = $this->pagelist([
			'templates'       => ['album', 'note'],
			'templatesIgnore' => ['note']
		]);

		$this->assertSame(['album'], $field->blueprintNames());
	}

	public function testBlueprintNamesWithoutTemplates(): void
	{
		// without any template, every blueprint can be created
		$field = $this->pagelist();

		$this->assertSame(['album', 'default', 'note'], $field->blueprintNames());
	}

	public function testBlueprints(): void
	{
		$field = $this->pagelist(['templates' => ['album']]);

		$this->assertSame([
			['name' => 'album', 'title' => 'Album']
		], $field->blueprints());
	}

	public function testBlueprintsWithMissingBlueprint(): void
	{
		// a blueprint that cannot be loaded still needs
		// an entry for the create dialog
		$field = $this->pagelist(['templates' => ['does-not-exist']]);

		$this->assertSame([
			['name' => 'does-not-exist', 'title' => 'Does-not-exist']
		], $field->blueprints());
	}

	public function testAddWithAllStatuses(): void
	{
		$this->assertTrue($this->pagelist()->add());
	}

	public function testAddDisabled(): void
	{
		$this->assertFalse($this->pagelist(['create' => false])->add());
	}

	public function testAddWhenFull(): void
	{
		$this->assertFalse($this->pagelist(['max' => 4])->add());
	}

	public function testAddWithMatchingStatus(): void
	{
		// the album blueprint creates listed pages
		$field = $this->pagelist([
			'status'    => 'listed',
			'templates' => ['album']
		]);

		$this->assertTrue($field->add());
	}

	public function testAddWithDifferentStatus(): void
	{
		// new notes are drafts and would not show up in a listed list
		$field = $this->pagelist([
			'status'    => 'listed',
			'templates' => ['note']
		]);

		$this->assertFalse($field->add());
	}

	public function testAddWithMixedStatuses(): void
	{
		$field = $this->pagelist([
			'status'    => 'listed',
			'templates' => ['album', 'note']
		]);

		$this->assertFalse($field->add());
	}

	public function testAddWithMissingBlueprint(): void
	{
		// a blueprint that cannot be loaded creates a draft
		$field = $this->pagelist([
			'status'    => 'draft',
			'templates' => ['does-not-exist']
		]);

		$this->assertTrue($field->add());

		$field = $this->pagelist([
			'status'    => 'listed',
			'templates' => ['does-not-exist']
		]);

		$this->assertFalse($field->add());
	}

	public function testColumnsWithoutTableLayout(): void
	{
		$this->assertSame([], $this->pagelist()->columns());
	}

	public function testColumnsWithStatusFlag(): void
	{
		$field = $this->pagelist(['layout' => 'table']);

		$this->assertSame(
			['image', 'title', 'flag'],
			array_keys($field->columns())
		);
	}

	public function testSortableDefault(): void
	{
		$this->assertTrue($this->pagelist()->sortable());
	}

	public function testSortableWithUnsortableStatus(): void
	{
		$this->assertFalse($this->pagelist(['status' => 'draft'])->sortable());
		$this->assertFalse($this->pagelist(['status' => 'unlisted'])->sortable());
	}

	public function testSortableWithSortableStatus(): void
	{
		$this->assertTrue($this->pagelist(['status' => 'listed'])->sortable());
		$this->assertTrue($this->pagelist(['status' => 'published'])->sortable());
	}

	public function testParentModelInvalid(): void
	{
		// a user is a valid model, but not a valid parent for pages
		$field = $this->pagelist(['parent' => 'kirby.user']);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The parent is invalid. You must choose the site or a page as parent.');

		$field->parentModel();
	}

	public function testErrorsMin(): void
	{
		$field = $this->pagelist(['min' => 10]);

		$this->assertSame(
			'The "Albums" section requires at least 10 pages',
			$field->errors()['min']
		);
	}

	public function testApi(): void
	{
		$patterns = array_column($this->pagelist()->api(), 'pattern');
		$this->assertSame(['', 'delete'], $patterns);
	}

	public function testState(): void
	{
		$state = $this->pagelist()->state();

		$this->assertArrayHasKey('add', $state);
		$this->assertArrayHasKey('columns', $state);
		$this->assertArrayHasKey('pagination', $state);
		$this->assertArrayHasKey('sortable', $state);
		$this->assertCount(4, $state['models']);
	}
}
