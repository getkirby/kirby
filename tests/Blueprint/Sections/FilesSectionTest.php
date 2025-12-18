<?php

namespace Kirby\Blueprint;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use Kirby\Toolkit\Locale;

class FilesSectionTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FilesSection';

	public function setUp(): void
	{
		Dir::make(static::TMP);
		$this->app();
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
		App::destroy();
	}

	public function app(array $props = [])
	{
		$this->app = new App(array_replace_recursive([
			'roots' => [
				'index' => static::TMP
			]
		], $props));

		// The file section will always be empty for
		// unauthorized users
		$this->app->impersonate('kirby');

		return $this->app;
	}

	public function testAccept(): void
	{
		$section = new Section('files', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'template' => 'note'
		]);

		$this->assertSame('*', $section->accept());
	}

	public function testBatchDefault(): void
	{
		$section = new Section('files', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertFalse($section->batch());
		$this->assertFalse($section->toArray()['options']['batch']);
	}

	public function testBatchDisabled(): void
	{
		$section = new Section('files', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'batch' => false
		]);

		$this->assertFalse($section->batch());
		$this->assertFalse($section->toArray()['options']['batch']);
	}

	public function testBatchEnabled(): void
	{
		$section = new Section('files', [
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
		$section = new Section('files', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'label' => 'Test'
		]);

		$this->assertSame('Test', $section->headline());

		// translated headline
		$section = new Section('files', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'label' => [
				'en' => 'Files',
				'de' => 'Dateien'
			]
		]);

		$this->assertSame('Files', $section->headline());
	}

	public function testHeadlineFromName(): void
	{
		// single label
		$section = new Section('files', [
			'name'  => 'photoGallery',
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertSame('Photo gallery', $section->headline());
	}

	public function testMax(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'a.jpg'
				],
				[
					'filename' => 'b.jpg'
				]
			]
		]);

		// already reached the max
		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model,
			'max'   => 2
		]);

		$this->assertFalse($section->upload());

		// one left
		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model,
			'max'   => 3
		]);

		$this->assertFalse($section->upload()['multiple']);

		// no max
		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model,
		]);

		$this->assertTrue($section->upload()['multiple']);
	}

	public function testParent(): void
	{
		$app = new App([
			'site' => [
				'children' => [
					[
						'slug' => 'a'
					],
					[
						'slug' => 'b'
					]
				]
			]
		]);

		$a = $app->page('a');
		$b = $app->page('b');

		// same parent
		$section = new Section('files', [
			'model' => $a,
		]);

		$this->assertNull($section->link());
		$this->assertSame($a, $section->parent());
		$this->assertSame('pages/a/files', $section->upload()['api']);

		// different parent
		$section = new Section('files', [
			'model'  => $a,
			'parent' => 'site.find("b")'
		]);

		$this->assertSame('/pages/b', $section->link());
		$this->assertSame($b, $section->parent());
		$this->assertSame('pages/b/files', $section->upload()['api']);
	}

	public function testParentCollectionFail(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The parent for the section "files" has to be a page, site or user object');

		$app = new App([
			'site' => [
				'children' => [
					[
						'slug' => 'a'
					],
					[
						'slug' => 'b'
					]
				]
			]
		]);

		$section = new Section('files', [
			'model'  => $app->page('a'),
			'parent' => 'site.index'
		]);
		$section->parentModel();
	}

	public function testEmpty(): void
	{
		$section = new Section('files', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $section->empty());
	}

	public function testTranslatedEmpty(): void
	{
		$section = new Section('files', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);

		$this->assertSame('Test', $section->empty());
	}

	public function testDragText(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'a.jpg',
					'content'  => ['uuid' => 'test-a']
				],
				[
					'filename' => 'b.jpg',
					'content'  => ['uuid' => 'test-b']
				]
			]
		]);

		// already reached the max
		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model
		]);

		$data = $section->data();
		$this->assertSame('(image: file://test-a)', $data[0]['dragText']);
	}

	public function testDragTextWithDifferentParent(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content' => ['uuid' => 'test-file-a']
							],
							[
								'filename' => 'b.jpg'
							]
						]
					],
					[
						'slug' => 'b'
					]
				]
			]
		]);
		$app->impersonate('kirby');

		// already reached the max
		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $app->page('b'),
			'parent' => 'site.find("a")'
		]);

		$data = $section->data();
		$this->assertSame('(image: file://test-file-a)', $data[0]['dragText']);
	}

	public function testHelp(): void
	{
		// single help
		$section = new Section('files', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'help'  => 'Test'
		]);

		$this->assertSame('<p>Test</p>', $section->help());

		// translated help
		$section = new Section('files', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'help' => [
				'en' => 'Information',
				'de' => 'Informationen'
			]
		]);

		$this->assertSame('<p>Information</p>', $section->help());
	}

	public function testSortBy(): void
	{
		$this->app->impersonate('kirby');

		$locale = Locale::get();
		setlocale(LC_ALL, ['de_DE.ISO8859-1', 'de_DE']);

		$model = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'z.jpg'
				],
				[
					'filename' => 'ä.jpg'
				],
				[
					'filename' => 'b.jpg'
				]
			]
		]);

		// no settings
		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model
		]);
		$this->assertSame('b.jpg', $section->data()[0]['filename']);
		$this->assertSame('z.jpg', $section->data()[1]['filename']);
		$this->assertSame('ä.jpg', $section->data()[2]['filename']);

		// custom sorting direction
		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'sortBy' => 'filename desc'
		]);
		$this->assertSame('ä.jpg', $section->data()[0]['filename']);
		$this->assertSame('z.jpg', $section->data()[1]['filename']);
		$this->assertSame('b.jpg', $section->data()[2]['filename']);

		// custom flag
		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'sortBy' => 'filename SORT_LOCALE_STRING'
		]);
		$this->assertSame('ä.jpg', $section->data()[0]['filename']);
		$this->assertSame('b.jpg', $section->data()[1]['filename']);
		$this->assertSame('z.jpg', $section->data()[2]['filename']);

		// flag & sorting direction
		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'sortBy' => 'filename desc SORT_LOCALE_STRING'
		]);
		$this->assertSame('z.jpg', $section->data()[0]['filename']);
		$this->assertSame('b.jpg', $section->data()[1]['filename']);
		$this->assertSame('ä.jpg', $section->data()[2]['filename']);

		Locale::set($locale);
	}

	public function testSortable(): void
	{
		$section = new Section('files', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertTrue($section->sortable());
	}

	public function testDisableSortable(): void
	{
		$section = new Section('files', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'sortable' => false
		]);

		$this->assertFalse($section->sortable());
	}

	public function testDisableSortableWhenSortBy(): void
	{
		$section = new Section('files', [
			'name'   => 'test',
			'model'  => new Page(['slug' => 'test']),
			'sortBy' => 'filename desc'
		]);

		$this->assertFalse($section->sortable());
	}

	public function testFlip(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'c.jpg'
				],
				[
					'filename' => 'a.jpg'
				],
				[
					'filename' => 'b.jpg'
				]
			]
		]);

		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model,
			'flip'  => true
		]);

		$this->assertSame('c.jpg', $section->data()[0]['filename']);
		$this->assertSame('b.jpg', $section->data()[1]['filename']);
		$this->assertSame('a.jpg', $section->data()[2]['filename']);
	}

	public function testTranslatedInfo(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg']
			]
		]);

		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model,
			'info'  => [
				'en' => 'en: {{ file.page.title }}',
				'de' => 'de: {{ file.page.title }}'
			]
		]);

		$this->assertSame('en: {{ file.page.title }}', $section->info());
		$this->assertSame('en: test', $section->data()[0]['info']);
		$this->assertSame('en: test', $section->data()[1]['info']);
	}

	public function testTranslatedText(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg']
			]
		]);

		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model,
			'text'  => [
				'en' => 'en: {{ file.filename }}',
				'de' => 'de: {{ file.filename }}'
			]
		]);

		$this->assertSame('en: {{ file.filename }}', $section->text());
		$this->assertSame('en: a.jpg', $section->data()[0]['text']);
		$this->assertSame('en: b.jpg', $section->data()[1]['text']);
	}

	public function testSearchDefault(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'mount-bike.jpg'],
				['filename' => 'mountain.jpg'],
				['filename' => 'bike.jpg']
			]
		]);

		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model
		]);

		$this->assertCount(3, $section->data());
	}

	public function testSearchWithNoQuery(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'mount-bike.jpg'],
				['filename' => 'mountain.jpg'],
				['filename' => 'bike.jpg']
			]
		]);

		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true
		]);

		$this->assertCount(3, $section->data());
	}

	public function testSearchWithQuery1(): void
	{
		$app = $this->app->clone([
			'request' => [
				'query' => ['searchterm' => 'bike']
			]
		]);
		$app->impersonate('kirby');

		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'mount-bike.jpg'],
				['filename' => 'mountain.jpg'],
				['filename' => 'bike.jpg']
			]
		]);

		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true
		]);

		$this->assertCount(2, $section->data());
		$this->assertSame('bike.jpg', $section->data()[0]['filename']);
		$this->assertSame('mount-bike.jpg', $section->data()[1]['filename']);
	}

	public function testSearchWithQuery2(): void
	{
		$app = $this->app->clone([
			'request' => [
				'query' => ['searchterm' => 'mount']
			]
		]);
		$app->impersonate('kirby');

		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'mount-bike.jpg'],
				['filename' => 'mountain.jpg'],
				['filename' => 'bike.jpg']
			]
		]);

		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true
		]);

		$this->assertCount(2, $section->data());
		$this->assertSame('mount-bike.jpg', $section->data()[0]['filename']);
		$this->assertSame('mountain.jpg', $section->data()[1]['filename']);
	}

	public function testSearchWithQuery3(): void
	{
		$app = $this->app->clone([
			'request' => [
				'query' => ['searchterm' => 'mountain']
			]
		]);
		$app->impersonate('kirby');

		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'mount-bike.jpg'],
				['filename' => 'mountain.jpg'],
				['filename' => 'bike.jpg']
			]
		]);

		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true

		]);

		$this->assertCount(1, $section->data());
		$this->assertSame('mountain.jpg', $section->data()[0]['filename']);
	}

	public function testSearchWithFlip(): void
	{
		$app = $this->app->clone([
			'request' => [
				'query' => ['searchterm' => 'bike']
			]
		]);
		$app->impersonate('kirby');

		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'bike.jpg'],
				['filename' => 'mountain.jpg'],
				['filename' => 'mount-bike.jpg']
			]
		]);

		$section = new Section('files', [
			'flip'   => true,
			'name'   => 'test',
			'model'  => $model,
			'search' => true
		]);

		$this->assertCount(2, $section->data());
		$this->assertSame('bike.jpg', $section->data()[0]['filename']);
		$this->assertSame('mount-bike.jpg', $section->data()[1]['filename']);
	}

	public function testSearchWithSortBy(): void
	{
		$app = $this->app->clone([
			'request' => [
				'query' => ['searchterm' => 'bike']
			]
		]);
		$app->impersonate('kirby');

		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'bike.jpg'],
				['filename' => 'mountain.jpg'],
				['filename' => 'mount-bike.jpg']
			]
		]);

		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'search' => true,
			'sortBy' => 'filename desc',
		]);

		$this->assertCount(2, $section->data());
		$this->assertSame('bike.jpg', $section->data()[0]['filename']);
		$this->assertSame('mount-bike.jpg', $section->data()[1]['filename']);
	}

	public function testTableLayout(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'mount-bike.jpg'],
			]
		]);

		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'layout' => 'table'
		]);

		$this->assertSame('table', $section->layout());

		$data = $section->data();
		$item = $data[0];

		$this->assertSame('', $item['info']);
		$this->assertSame([
			'text' => 'mount-bike.jpg',
			'href' => '/pages/test/files/mount-bike.jpg'
		], $item['title']);
	}

	public function testTableLayoutWithCustomColumns(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'mount-bike.jpg',
					'content'  => ['alt' => 'Alt test']
				],
			]
		]);

		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'layout' => 'table',
			'columns' => [
				'alt' => [
					'label' => 'Alt',
					'type'  => 'text'
				]
			]
		]);

		$this->assertSame('Alt test', $section->data()[0]['alt']);
	}

	public function testOptions(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'mount-bike.jpg',
				],
			]
		]);

		$section = new Section('files', [
			'name'   => 'test',
			'model'  => $model,
			'layout' => 'list',
		]);

		$options = $section->toArray()['options'];

		$this->assertSame([], $options['columns']);
		$this->assertNull($options['link']);
	}

	public function testSort(): void
	{
		$model = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg'],
			]
		]);

		// fewer files than limit
		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model,
			'limit'   => 5
		]);

		$this->assertSame(4, $section->upload()['attributes']['sort']);

		// more files than limit
		$section = new Section('files', [
			'name'  => 'test',
			'model' => $model,
			'limit'   => 2
		]);

		$this->assertSame(4, $section->upload()['attributes']['sort']);
	}

	public function testUpload(): void
	{
		$section = new Section('files', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
		]);

		$expected = [
			'accept' => null,
			'api' => 'pages/test/files',
			'attributes' => [
				'sort' => 1,
				'template' => null,
			],
			'max' => null,
			'multiple' => true,
			'preview' => []
		];

		$this->assertSame($expected, $section->upload());
	}

	public function testUploadSwitchedOff(): void
	{
		$section = new Section('files', [
			'name'   => 'test',
			'model'  => new Page(['slug' => 'test']),
			'create' => false
		]);

		$this->assertFalse($section->upload());
	}
}
