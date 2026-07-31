<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Form\Field;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FileListField::class)]
#[CoversClass(ModelListField::class)]
class FileListFieldTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Form.Fields.FileListField';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'blueprints' => [
				'files/image' => [
					'fields' => [
						'alt' => ['type' => 'text']
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'photography',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => ['alt' => 'Alt A'],
								'template' => 'image'
							],
							[
								'filename' => 'b.jpg',
								'content'  => ['alt' => 'Alt B'],
								'template' => 'image'
							],
							[
								'filename' => 'c.jpg',
								'content'  => ['alt' => 'Alt C'],
								'template' => 'image'
							]
						],
						'children' => [
							['slug' => 'trees']
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

	protected function filelist(array $attr = []): FileListField
	{
		return Field::factory('filelist', [
			'model' => $this->app->page('photography'),
			'name'  => 'gallery',
			...$attr
		]);
	}

	public function testType(): void
	{
		$this->assertSame('filelist', $this->filelist()->type());
	}

	public function testHasNoValue(): void
	{
		$field = $this->filelist();

		$this->assertFalse($field->hasValue());
		$this->assertFalse($field->props()['saveable']);
	}

	public function testData(): void
	{
		$data = $this->filelist()->data();

		$this->assertCount(3, $data);
		$this->assertSame('photography/a.jpg', $data[0]['id']);
		$this->assertSame('a.jpg', $data[0]['text']);
	}

	public function testTotal(): void
	{
		$this->assertSame(3, $this->filelist()->total());
	}

	public function testTextDefault(): void
	{
		$this->assertSame('{{ file.filename }}', $this->filelist()->text());
	}

	public function testTextCustom(): void
	{
		$field = $this->filelist(['text' => '{{ file.id }}']);
		$this->assertSame('{{ file.id }}', $field->text());
	}

	public static function layoutProvider(): array
	{
		return [
			[null, 'list'],
			['list', 'list'],
			['cards', 'cards'],
			['cardlets', 'cardlets'],
			['table', 'table'],
			['does-not-exist', 'list']
		];
	}

	#[DataProvider('layoutProvider')]
	public function testLayout(string|null $layout, string $expected): void
	{
		$field = $this->filelist(['layout' => $layout]);
		$this->assertSame($expected, $field->layout());
	}

	public function testColumnsWithoutTableLayout(): void
	{
		$this->assertSame([], $this->filelist()->columns());
	}

	public function testColumns(): void
	{
		$field = $this->filelist(['layout' => 'table']);

		$this->assertSame(['image', 'title'], array_keys($field->columns()));
	}

	public function testColumnsWithoutImage(): void
	{
		$field = $this->filelist([
			'image'  => false,
			'layout' => 'table'
		]);

		$this->assertSame(['title'], array_keys($field->columns()));
	}

	public function testColumnsWithoutText(): void
	{
		$field = $this->filelist([
			'layout' => 'table',
			'text'   => ''
		]);

		$this->assertSame(['image'], array_keys($field->columns()));
	}

	public function testColumnsWithZeroAsText(): void
	{
		$field = $this->filelist([
			'layout' => 'table',
			'text'   => '0'
		]);

		$this->assertSame(['image', 'title'], array_keys($field->columns()));
	}

	public function testColumnsWithInfo(): void
	{
		$field = $this->filelist([
			'info'   => '{{ file.template }}',
			'layout' => 'table'
		]);

		$this->assertSame(['image', 'title', 'info'], array_keys($field->columns()));
	}

	public function testColumnsCustom(): void
	{
		$field = $this->filelist([
			'columns' => [
				'alt'      => true,
				'caption'  => ['label' => 'Text'],
				'hidden'   => false,
				'title'    => ['label' => 'Filename']
			],
			'layout' => 'table'
		]);

		$columns = $field->columns();

		$this->assertSame(['image', 'title', 'alt', 'caption'], array_keys($columns));

		// a column without a label falls back to the column name
		$this->assertSame('Alt', $columns['alt']['label']);
		$this->assertSame('Text', $columns['caption']['label']);

		// the auto column can be extended
		$this->assertSame('Filename', $columns['title']['label']);
		$this->assertSame('url', $columns['title']['type']);
	}

	public function testColumnsWithTypes(): void
	{
		$field = $this->filelist([
			'columns' => ['alt' => true],
			'layout'  => 'table'
		]);

		$this->assertSame('text', $field->columnsWithTypes()['alt']['type']);
	}

	public function testColumnsWithTypesWithoutFiles(): void
	{
		$field = $this->filelist([
			'columns'  => ['alt' => true],
			'layout'   => 'table',
			'template' => 'does-not-exist'
		]);

		$this->assertSame($field->columns(), $field->columnsWithTypes());
	}

	public function testDataWithTableLayout(): void
	{
		$field = $this->filelist([
			'columns' => [
				'alt'      => true,
				'filename' => ['value' => '{{ file.filename }}']
			],
			'layout' => 'table'
		]);

		$item = $field->data()[0];

		$this->assertSame([
			'text' => 'a.jpg',
			'href' => '/pages/photography/files/a.jpg'
		], $item['title']);

		// resolved from the content
		$this->assertSame('Alt A', $item['alt']);

		// resolved from the column query
		$this->assertSame('a.jpg', $item['filename']);
	}

	public function testLimitDefault(): void
	{
		$this->assertSame(20, $this->filelist()->limit());
	}

	public function testPagination(): void
	{
		$field = $this->filelist(['limit' => 2]);

		$this->assertSame([
			'limit'  => 2,
			'offset' => 0,
			'page'   => 1,
			'total'  => 3
		], $field->pagination());

		$this->assertCount(2, $field->data());
	}

	public function testPaginationFromRequest(): void
	{
		$_GET['page'] = '2';

		$field = $this->filelist(['limit' => 2]);

		$this->assertSame(2, $field->pagination()['page']);
		$this->assertCount(1, $field->data());
	}

	public function testParentModelDefault(): void
	{
		$this->assertIsPage('photography', $this->filelist()->parentModel());
	}

	public function testParentModelFromQuery(): void
	{
		$field = $this->filelist(['parent' => 'page.children.first']);
		$this->assertIsPage('photography/trees', $field->parentModel());
	}

	public function testParentModelNotFound(): void
	{
		$field = $this->filelist(['parent' => 'site.find("does-not-exist")']);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The parent for the query "site.find("does-not-exist")" cannot be found in the field "gallery"');

		$field->parentModel();
	}

	public function testParentModelInvalidType(): void
	{
		$field = $this->filelist(['parent' => 'site.index']);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The parent for the field "gallery" has to be a page, site, file or user object');

		$field->parentModel();
	}

	public function testLink(): void
	{
		$this->assertNull($this->filelist()->link());

		$field = $this->filelist(['parent' => 'page.children.first']);
		$this->assertSame('/pages/photography+trees', $field->link());
	}

	public function testSearchDisabled(): void
	{
		$_GET['searchterm'] = 'a';

		$field = $this->filelist();

		$this->assertNull($field->searchterm());
		$this->assertCount(3, $field->data());
	}

	public function testSearchEnabled(): void
	{
		$_GET['searchterm'] = 'a.jpg';

		$field = $this->filelist(['search' => true]);

		$this->assertSame('a.jpg', $field->searchterm());
		$this->assertCount(1, $field->data());
	}

	public function testSortableDefault(): void
	{
		$this->assertTrue($this->filelist()->sortable());
	}

	public function testSortableDisabled(): void
	{
		$field = $this->filelist(['sortable' => false]);
		$this->assertFalse($field->sortable());
	}

	public function testSortableWithQuery(): void
	{
		$field = $this->filelist(['query' => 'page.images']);
		$this->assertFalse($field->sortable());
	}

	public function testSortableWithSortBy(): void
	{
		$field = $this->filelist(['sortBy' => 'filename desc']);
		$this->assertFalse($field->sortable());
	}

	public function testSortableWithFlip(): void
	{
		$field = $this->filelist(['flip' => true]);
		$this->assertFalse($field->sortable());
	}

	public function testSortableWhileSearching(): void
	{
		$_GET['searchterm'] = 'a';

		$field = $this->filelist(['search' => true]);
		$this->assertFalse($field->sortable());
	}

	public function testErrorsWithoutLimits(): void
	{
		$this->assertSame([], $this->filelist()->errors());
	}

	public function testErrorsMin(): void
	{
		$field = $this->filelist(['min' => 5]);

		$this->assertFalse($field->validateMin());
		$this->assertSame(
			'The "Gallery" section requires at least 5 files',
			$field->errors()['min']
		);
	}

	public function testErrorsMax(): void
	{
		$field = $this->filelist(['max' => 2]);

		$this->assertFalse($field->validateMax());
		$this->assertSame(
			'You must not add more than 2 files to the "Gallery" section',
			$field->errors()['max']
		);
	}

	public function testErrorsWhenInactive(): void
	{
		$field = $this->filelist([
			'min'  => 5,
			'when' => ['other' => 'value']
		]);

		$this->assertSame([], $field->errors());
	}

	public function testIsFull(): void
	{
		$this->assertFalse($this->filelist()->isFull());
		$this->assertTrue($this->filelist(['max' => 3])->isFull());
	}

	public function testUpload(): void
	{
		$this->assertSame([
			'accept'     => null,
			'api'        => 'pages/photography/files',
			'attributes' => [
				'sort'     => 4,
				'template' => null
			],
			'max'        => null,
			'multiple'   => true,
			'preview'    => []
		], $this->filelist()->upload());
	}

	public function testUploadWithMax(): void
	{
		$upload = $this->filelist(['max' => 5])->upload();

		// only the remaining files can be uploaded
		$this->assertSame(2, $upload['max']);
	}

	public function testUploadWithTemplate(): void
	{
		$upload = $this->filelist(['template' => 'image'])->upload();

		$this->assertSame('image', $upload['attributes']['template']);
	}

	public function testUploadWhenNotSortable(): void
	{
		$upload = $this->filelist(['sortable' => false])->upload();

		$this->assertArrayNotHasKey('sort', array_filter($upload['attributes']));
	}

	public function testUploadDisabled(): void
	{
		$this->assertFalse($this->filelist(['create' => false])->upload());
	}

	public function testUploadWhenFull(): void
	{
		$this->assertFalse($this->filelist(['max' => 3])->upload());
	}

	public function testAccept(): void
	{
		// without a template, the upload dialog accepts anything
		$this->assertNull($this->filelist()->accept());

		$field = $this->filelist(['template' => 'image']);
		$this->assertSame('*', $field->accept());
	}

	public function testTemplate(): void
	{
		$field = $this->filelist(['template' => 'cover']);
		$this->assertSame('cover', $field->template());
	}

	public function testApi(): void
	{
		$patterns = array_column($this->filelist()->api(), 'pattern');
		$this->assertSame(['', 'delete', 'sort'], $patterns);
	}

	public function testApiProps(): void
	{
		$_GET['page'] = '2';

		$field  = $this->filelist(['limit' => 2]);
		$routes = array_column($field->api(), null, 'pattern');
		$props  = $routes['']['action']();

		$this->assertSame(2, $props['pagination']['page']);
		$this->assertSame(3, $props['pagination']['total']);
		$this->assertCount(1, $props['data']);
	}

	public function testDeleteSelected(): void
	{
		$field = $this->filelist(['batch' => true]);

		$this->assertTrue($field->deleteSelected(['photography/a.jpg']));
		$this->assertCount(2, $this->app->page('photography')->files());
	}

	public function testDeleteSelectedWithoutIds(): void
	{
		$field = $this->filelist();
		$this->assertTrue($field->deleteSelected([]));
	}

	public function testDeleteSelectedWithoutBatch(): void
	{
		$field = $this->filelist();

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The field does not support batch actions');

		$field->deleteSelected(['photography/a.jpg']);
	}

	public function testDeleteSelectedBelowMin(): void
	{
		$field = $this->filelist([
			'batch' => true,
			'min'   => 3
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The "Gallery" section requires at least 3 files');

		$field->deleteSelected(['photography/a.jpg']);
	}
}
