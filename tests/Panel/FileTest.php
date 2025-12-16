<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File as ModelFile;
use Kirby\Cms\Page as ModelPage;
use Kirby\Cms\Site as ModelSite;
use Kirby\Cms\User as ModelUser;
use Kirby\Content\Lock;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class FileForceLocked extends ModelFile
{
	public function lock(): Lock
	{
		return new Lock(
			user: new ModelUser(['email' => 'test@getkirby.com']),
			modified: time()
		);
	}
}

#[CoversClass(\Kirby\Panel\File::class)]
#[CoversClass(Model::class)]
class FileTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP = KIRBY_TMP_DIR . '/Panel.File';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	protected function panel(array $props = [])
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'test.jpg', ...$props],
			]
		]);
		return new File($page->file('test.jpg'));
	}

	public function testBreadcrumbForSiteFile(): void
	{
		$site = new ModelSite([
			'files' => [
				['filename' => 'test.jpg'],
			]
		]);

		$file = new File($site->file('test.jpg'));
		$this->assertSame([
			[
				'label' => 'test.jpg',
				'link'  => '/site/files/test.jpg'
			]
		], $file->breadcrumb());
	}

	public function testBreadcrumbForPageFile(): void
	{
		$page = new ModelPage([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			],
			'files' => [
				['filename' => 'test.jpg'],
			]
		]);

		$file = new File($page->file('test.jpg'));
		$this->assertSame([
			[
				'label' => 'Test',
				'link'  => '/pages/test'
			],
			[
				'label' => 'test.jpg',
				'link'  => '/pages/test/files/test.jpg'
			]
		], $file->breadcrumb());
	}

	public function testBreadcrumbForUserFile(): void
	{
		$user = new ModelUser([
			'id'    => 'test',
			'email' => 'test@getkirby.com',
			'files' => [
				['filename' => 'test.jpg'],
			]
		]);

		$file = new File($user->file('test.jpg'));
		$this->assertSame([
			[
				'label' => 'test@getkirby.com',
				'link'  => '/users/test'
			],
			[
				'label' => 'test.jpg',
				'link'  => '/users/test/files/test.jpg'
			]
		], $file->breadcrumb());
	}

	public function testButtons(): void
	{
		$this->assertSame([
			'k-open-view-button',
			'k-settings-view-button',
			'k-languages-view-button',
		], array_column($this->panel()->buttons(), 'component'));
	}

	public function testDragText(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'test.jpg',
					'content'  => ['uuid' => 'test-jpg']
				],
				[
					'filename' => 'test.mp4',
					'content'  => ['uuid' => 'test-mp4']
				],
				[
					'filename' => 'test.pdf',
					'content'  => ['uuid' => 'test-pdf']
				]
			]
		]);

		$panel = new File($page->file('test.pdf'));
		$this->assertSame('(file: file://test-pdf)', $panel->dragText());

		$panel = new File($page->file('test.mp4'));
		$this->assertSame('(video: file://test-mp4)', $panel->dragText());

		$panel = new File($page->file('test.jpg'));
		$this->assertSame('(image: file://test-jpg)', $panel->dragText());
	}

	public function testDragTextMarkdown(): void
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'kirbytext' => false
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							[
								'filename' => 'test.jpg',
								'content'  => ['uuid' => 'test-jpg']
							],
							[
								'filename' => 'test.mp4',
								'content'  => ['uuid' => 'test-mp4']
							],
							[
								'filename' => 'test.pdf',
								'content'  => ['uuid' => 'test-pdf']
							]
						]
					]
				]
			]
		]);

		$file = $app->page('test')->file('test.jpg');
		$this->assertSame('![](//@/file/test-jpg)', $file->panel()->dragText());

		$file = $app->page('test')->file('test.mp4');
		$this->assertSame('[test.mp4](//@/file/test-mp4)', $file->panel()->dragText());

		$file = $app->page('test')->file('test.pdf');
		$this->assertSame('[test.pdf](//@/file/test-pdf)', $file->panel()->dragText());
	}

	public function testDragTextCustomMarkdown(): void
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'kirbytext' => false,
					'markdown' => [
						'fileDragText' => function (ModelFile $file, string $url) {
							if ($file->extension() === 'heic') {
								return sprintf('![](%s)', $url);
							}

							return null;
						},
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							[
								'filename' => 'test.heic',
								'content'  => ['uuid' => 'test-heic']
							],
							[
								'filename' => 'test.jpg',
								'content'  => ['uuid' => 'test-jpg']
							]
						]
					]
				]
			]
		]);

		// Custom function does not match and returns null, default case
		$panel = new File($app->page('test')->file('test.jpg'));
		$this->assertSame('![](//@/file/test-jpg)', $panel->dragText());

		// Custom function should return image tag for heic
		$panel = new File($app->page('test')->file('test.heic'));
		$this->assertSame('![](//@/file/test-heic)', $panel->dragText());
	}

	public function testDragTextCustomKirbytext(): void
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'kirbytext' => [
						'fileDragText' => function (ModelFile $file, string $url) {
							if ($file->extension() === 'heic') {
								return sprintf('(image: %s)', $url);
							}

							return null;
						},
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							[
								'filename' => 'test.heic',
								'content'  => ['uuid' => 'test-heic']
							],
							[
								'filename' => 'test.jpg',
								'content'  => ['uuid' => 'test-jpg']
							]
						]
					]
				]
			]
		]);

		// Custom function does not match and returns null, default case
		$panel = new File($app->page('test')->file('test.jpg'));
		$this->assertSame('(image: file://test-jpg)', $panel->dragText());

		// Custom function should return image tag for heic
		$panel = new File($app->page('test')->file('test.heic'));
		$this->assertSame('(image: file://test-heic)', $panel->dragText());
	}

	public function testDropdownOption(): void
	{
		$page = new ModelPage([
			'slug' => 'test',
			'files' => [
				['filename' => 'test.jpg'],
			]
		]);

		$panel  = new File($page->file());
		$option = $panel->dropdownOption();

		$this->assertSame('image', $option['icon']);
		$this->assertSame('test.jpg', $option['text']);
		$this->assertSame('/pages/test/files/test.jpg', $option['link']);
	}

	public function testImage(): void
	{
		$page = new ModelPage([
			'slug' => 'test'
		]);

		$file = new ModelFile([
			'filename' => 'something.jpg',
			'parent'   => $page
		]);

		$image = (new File($file))->image();
		$this->assertSame('image', $image['icon']);
		$this->assertSame('orange-500', $image['color']);
		$this->assertSame('pattern', $image['back']);
		$this->assertArrayHasKey('url', $image);
	}

	public function testImageCover(): void
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'test.jpg']
				]
			]
		]);

		$file = $app->site()->image();

		$testImage = static::FIXTURES . '/image/test.jpg';
		F::copy($testImage, $app->site()->root() . '/test.jpg');

		$panel = new File($file);

		$hash = $file->mediaHash();

		// cover disabled as default
		$this->assertSame([
			'back' => 'pattern',
			'color' => 'orange-500',
			'cover' => false,
			'icon' => 'image',
			'url' => '/media/site/' . $hash . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => '/media/site/' . $hash . '/test-38x.jpg 38w, /media/site/' . $hash . '/test-76x.jpg 76w'
		], $panel->image());

		// cover enabled
		$this->assertSame([
			'back' => 'pattern',
			'color' => 'orange-500',
			'cover' => true,
			'icon' => 'image',
			'url' => '/media/site/' . $hash . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => '/media/site/' . $hash . '/test-38x38-crop.jpg 1x, /media/site/' . $hash . '/test-76x76-crop.jpg 2x'
		], $panel->image(['cover' => true]));
	}

	public function testImageStringQuery(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'test.jpg'],
				['filename' => 'foo.pdf']
			]
		]);

		// fallback to model itself
		$image = (new File($page->file()))->image('foo.bar');
		$this->assertNotEmpty($image);
	}

	public function testImageColor(): void
	{
		$page = new ModelPage(['slug' => 'test']);
		$file = new ModelFile([
			'filename' => 'something.doc',
			'parent'   => $page
		]);

		$this->assertSame('blue-500', $file->panel()->image()['color']);

		$file = new ModelFile([
			'filename' => 'something.mp4',
			'parent'   => $page
		]);

		$this->assertSame('yellow-500', $file->panel()->image()['color']);

		$file = new ModelFile([
			'filename' => 'something.foo',
			'parent'   => $page
		]);

		$this->assertSame('gray-500', $file->panel()->image()['color']);
	}

	public function testImageIcon(): void
	{
		$page = new ModelPage(['slug' => 'test']);
		$file = new ModelFile([
			'filename' => 'something.doc',
			'parent'   => $page
		]);

		$this->assertSame('pen', $file->panel()->image()['icon']);

		$file = new ModelFile([
			'filename' => 'something.mp4',
			'parent'   => $page
		]);

		$this->assertSame('video', $file->panel()->image()['icon']);

		$file = new ModelFile([
			'filename' => 'something.foo',
			'parent'   => $page
		]);

		$this->assertSame('file', $file->panel()->image()['icon']);
	}

	public function testIsFocusable(): void
	{
		$this->app->clone([
			'blueprints' => [
				'files/foo' => [
					'focus' => false
				]
			]
		]);


		$page = new ModelPage(['slug' => 'test']);

		// no update permission
		$file = new ModelFile([
			'filename' => 'test.jpg',
			'parent'   => $page,
		]);

		$this->assertFalse((new File($file))->isFocusable());

		// default for images (viewable)
		$file = new ModelFile([
			'filename' => 'test.jpg',
			'parent'   => $page,
		]);
		$file->kirby()->impersonate('kirby');

		$this->assertTrue((new File($file))->isFocusable());

		// default for others (not viewable)
		$file = new ModelFile([
			'filename' => 'test.mp4',
			'parent'   => $page,
		]);
		$file->kirby()->impersonate('kirby');

		$this->assertFalse((new File($file))->isFocusable());

		// blueprint option: false
		$file = new ModelFile([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'template' => 'foo',
		]);
		$file->kirby()->impersonate('kirby');

		$this->assertFalse((new File($file))->isFocusable());

		// editing secondary language
		$app = $this->app->clone([
			'languages' => [
				[
					'code' => 'en',
					'name' => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);

		$app->setCurrentLanguage('de');


		$file = new ModelFile([
			'filename' => 'test.jpg',
			'parent'   => $page,
		]);
		$file->kirby()->impersonate('kirby');

		$this->assertFalse((new File($file))->isFocusable());
	}

	public function testOptions(): void
	{
		$page = new ModelPage([
			'slug' => 'test'
		]);

		$file = new ModelFile([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$file->kirby()->impersonate('kirby');

		$expected = [
			'access'      	 => true,
			'changeName'     => true,
			'changeTemplate' => false,
			'create'         => true,
			'delete'         => true,
			'list'         	 => true,
			'read'           => true,
			'replace'        => true,
			'sort'           => true,
			'update'         => true,
		];

		$panel = new File($file);
		$this->assertSame($expected, $panel->options());
	}

	public function testOptionsWithLockedFile(): void
	{
		$page = new ModelPage([
			'slug' => 'test'
		]);

		$file = new FileForceLocked([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$file->kirby()->impersonate('kirby');

		// without override
		$expected = [
			'access'     	 => false,
			'changeName'     => false,
			'changeTemplate' => false,
			'create'         => false,
			'delete'         => false,
			'list'           => false,
			'read'           => false,
			'replace'        => false,
			'sort'           => false,
			'update'         => false,
		];

		$panel = new File($file);
		$this->assertSame($expected, $panel->options());

		// with override
		$expected = [
			'access'     	 => false,
			'changeName'     => false,
			'changeTemplate' => false,
			'create'         => false,
			'delete'         => true,
			'list'           => false,
			'read'           => false,
			'replace'        => false,
			'sort'           => false,
			'update'         => false,
		];

		$this->assertSame($expected, $panel->options(['delete']));
	}

	public function testOptionsDefaultReplaceOption(): void
	{
		$page = new ModelPage([
			'slug' => 'test'
		]);

		$file = new ModelFile([
			'filename' => 'test.js',
			'parent'   => $page
		]);
		$file->kirby()->impersonate('kirby');

		$expected = [
			'access'     	 => true,
			'changeName'     => true,
			'changeTemplate' => false,
			'create'         => true,
			'delete'         => true,
			'list'           => true,
			'read'           => true,
			'replace'        => false,
			'sort'           => true,
			'update'         => true,
		];

		$panel = new File($file);
		$this->assertSame($expected, $panel->options());
	}

	public function testOptionsAllowedReplaceOption(): void
	{
		$this->app->clone([
			'blueprints' => [
				'files/test' => [
					'name'   => 'test',
					'accept' => true
				]
			]
		]);

		$page = new ModelPage([
			'slug' => 'test'
		]);

		$file = new ModelFile([
			'filename' => 'test.js',
			'parent'   => $page,
			'template' => 'test',
		]);

		$file->kirby()->impersonate('kirby');

		$expected = [
			'access'     	 => true,
			'changeName'     => true,
			'changeTemplate' => false,
			'create'         => true,
			'delete'         => true,
			'list'           => true,
			'read'           => true,
			'replace'        => true,
			'sort'           => true,
			'update'         => true,
		];

		$panel = new File($file);
		$this->assertSame($expected, $panel->options());
	}

	public function testOptionsDisabledReplaceOption(): void
	{
		$this->app->clone([
			'blueprints' => [
				'files/restricted' => [
					'name'   => 'restricted',
					'accept' => [
						'type' => 'image'
					]
				]
			]
		]);

		$page = new ModelPage([
			'slug' => 'test'
		]);

		$file = new ModelFile([
			'filename' => 'test.js',
			'parent'   => $page,
			'template' => 'restricted',
		]);

		$file->kirby()->impersonate('kirby');

		$expected = [
			'access'     	 => true,
			'changeName'     => true,
			'changeTemplate' => false,
			'create'         => true,
			'delete'         => true,
			'list'           => true,
			'read'           => true,
			'replace'        => false,
			'sort'           => true,
			'update'         => true,
		];

		$panel = new File($file);
		$this->assertSame($expected, $panel->options());
	}

	public function testPath(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'test.jpg']
			]
		]);

		$panel = new File($page->file('test.jpg'));
		$this->assertSame('files/test.jpg', $panel->path());
	}

	public function testPickerDataDefault(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'test.jpg',
					'content'  => ['uuid' => 'test-file']
				]
			]
		]);

		$panel = new File($page->file('test.jpg'));
		$data  = $panel->pickerData();
		$this->assertSame('test.jpg', $data['filename']);
		$this->assertSame('(image: file://test-file)', $data['dragText']);
		$this->assertSame('test/test.jpg', $data['id']);
		$this->assertSame('image', $data['image']['icon']);
		$this->assertSame('/pages/test/files/test.jpg', $data['link']);
		$this->assertSame('test.jpg', $data['text']);
	}

	public function testPickerDataWithParams(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'test.jpg',
					'content' => [
						'alt' => 'From foo to the bar'
					]
				]
			]
		]);

		$panel = new File($page->file('test.jpg'));
		$data  = $panel->pickerData([
			'image' => [
				'ratio' => '1/1'
			],
			'text' => '{{ file.alt }}'
		]);

		$this->assertSame('test/test.jpg', $data['id']);
		$this->assertSame('1/1', $data['image']['ratio']);
		$this->assertSame('From foo to the bar', $data['text']);
	}

	public function testPickerDataSameModel(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'test.jpg',
					'content'  => ['uuid' => 'test-file']
				]
			]
		]);

		$panel = new File($page->file('test.jpg'));
		$data  = $panel->pickerData(['model' => $page]);

		$this->assertSame('(image: file://test-file)', $data['dragText']);
		$this->assertSame('test.jpg', $data['id']);
	}

	public function testPickerDataDifferentModel(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'test.jpg', 'content' => ['uuid' => 'test-file']]
			]
		]);

		$model = new ModelPage([
			'slug'  => 'foo'
		]);

		$panel = new File($page->file('test.jpg'));
		$data  = $panel->pickerData(['model' => $model]);

		$this->assertSame('(image: file://test-file)', $data['dragText']);
	}

	public function testProps(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'test.jpg']
			]
		]);

		$panel = new File($page->file('test.jpg'));
		$props = $panel->props();

		$this->assertArrayHasKey('model', $props);
		$this->assertArrayHasKey('dimensions', $props['model']);
		$this->assertArrayHasKey('extension', $props['model']);
		$this->assertArrayHasKey('filename', $props['model']);
		$this->assertArrayHasKey('id', $props['model']);
		$this->assertArrayHasKey('mime', $props['model']);
		$this->assertArrayHasKey('niceSize', $props['model']);
		$this->assertArrayHasKey('parent', $props['model']);
		$this->assertArrayHasKey('url', $props['model']);
		$this->assertArrayHasKey('template', $props['model']);
		$this->assertArrayHasKey('type', $props['model']);
		$this->assertArrayHasKey('preview', $props);

		// inherited props
		$this->assertArrayHasKey('blueprint', $props);
		$this->assertArrayHasKey('lock', $props);
		$this->assertArrayHasKey('permissions', $props);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertArrayHasKey('tabs', $props);
		$this->assertArrayHasKey('versions', $props);
	}

	public function testPropsPrevNext(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg']
			]
		]);

		$props = (new File($page->file('a.jpg')))->props();
		$this->assertNull($props['prev']());
		$this->assertSame('/pages/test/files/b.jpg', $props['next']()['link']);

		$props = (new File($page->file('b.jpg')))->props();
		$this->assertSame('/pages/test/files/a.jpg', $props['prev']()['link']);
		$this->assertSame('/pages/test/files/c.jpg', $props['next']()['link']);

		$props = (new File($page->file('c.jpg')))->props();
		$this->assertSame('/pages/test/files/b.jpg', $props['prev']()['link']);
		$this->assertNull($props['next']());
	}

	public function testPropsPrevNextWithSort(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg', 'content' => ['sort' => 2]],
				['filename' => 'b.jpg', 'content' => ['sort' => 1]],
				['filename' => 'c.jpg', 'content' => ['sort' => 3]]
			]
		]);

		$props = (new File($page->file('a.jpg')))->props();
		$this->assertSame('/pages/test/files/b.jpg', $props['prev']()['link']);
		$this->assertSame('/pages/test/files/c.jpg', $props['next']()['link']);

		$props = (new File($page->file('b.jpg')))->props();
		$this->assertNull($props['prev']());
		$this->assertSame('/pages/test/files/a.jpg', $props['next']()['link']);

		$props = (new File($page->file('c.jpg')))->props();
		$this->assertSame('/pages/test/files/a.jpg', $props['prev']()['link']);
		$this->assertNull($props['next']());
	}

	public function testPropsPrevNextWithTab(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg']
			]
		]);

		$_GET['tab'] = 'test';

		$prevNext = (new File($page->file('b.jpg')))->prevNext();
		$this->assertSame('/pages/test/files/a.jpg?tab=test', $prevNext['prev']()['link']);
		$this->assertSame('/pages/test/files/c.jpg?tab=test', $prevNext['next']()['link']);

		$_GET = [];
	}

	public function testUrl(): void
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'mother',
						'children' => [
							[
								'slug' => 'child',
								'files' => [
									['filename' => 'page-file.jpg'],
								]
							]
						]
					]
				],
				'files' => [
					['filename' => 'site-file.jpg']
				]
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'id'    => 'test',
					'files' => [
						['filename' => 'user-file.jpg']
					]
				]
			]
		]);

		// site file
		$file = $app->file('site-file.jpg');
		$panel = new File($file);

		$this->assertSame('https://getkirby.com/panel/site/files/site-file.jpg', $panel->url());
		$this->assertSame('/site/files/site-file.jpg', $panel->url(true));

		// page file
		$file = $app->file('mother/child/page-file.jpg');
		$panel = new File($file);

		$this->assertSame('https://getkirby.com/panel/pages/mother+child/files/page-file.jpg', $panel->url());
		$this->assertSame('/pages/mother+child/files/page-file.jpg', $panel->url(true));

		// user file
		$user = $app->user('test@getkirby.com');
		$file = $user->file('user-file.jpg');
		$panel = new File($file);

		$this->assertSame('https://getkirby.com/panel/users/test/files/user-file.jpg', $panel->url());
		$this->assertSame('/users/test/files/user-file.jpg', $panel->url(true));
	}

	public function testPrevNext(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg']
			]
		]);

		$prevNext = (new File($page->file('a.jpg')))->prevNext();
		$this->assertNull($prevNext['prev']());
		$this->assertSame('/pages/test/files/b.jpg', $prevNext['next']()['link']);

		$prevNext = (new File($page->file('b.jpg')))->prevNext();
		$this->assertSame('/pages/test/files/a.jpg', $prevNext['prev']()['link']);
		$this->assertSame('/pages/test/files/c.jpg', $prevNext['next']()['link']);

		$prevNext = (new File($page->file('c.jpg')))->prevNext();
		$this->assertSame('/pages/test/files/b.jpg', $prevNext['prev']()['link']);
		$this->assertNull($prevNext['next']());
	}

	public function testView(): void
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'test.jpg']
			]
		]);

		$panel = new File($page->file('test.jpg'));
		$view  = $panel->view();

		$this->assertArrayHasKey('props', $view);
		$this->assertSame('k-file-view', $view['component']);
		$this->assertSame('test.jpg', $view['title']);
		$this->assertSame('files', $view['search']);
		$breadcrumb = $view['breadcrumb']();
		$this->assertSame('test', $breadcrumb[0]['label']);
		$this->assertSame('test.jpg', $breadcrumb[1]['label']);
	}
}
