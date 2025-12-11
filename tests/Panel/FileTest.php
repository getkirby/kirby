<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File as ModelFile;
use Kirby\Cms\Page as ModelPage;
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

#[CoversClass(File::class)]
#[CoversClass(Model::class)]
class FileTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP = KIRBY_TMP_DIR . '/Panel.File';

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
			'back'   => 'pattern',
			'color'  => 'orange-500',
			'cover'  => false,
			'icon'   => 'image',
			'url'    => '/media/site/' . $hash . '/test.jpg',
			'src'    => Model::imagePlaceholder(),
			'srcset' => '/media/site/' . $hash . '/test-36x.jpg 36w, /media/site/' . $hash . '/test-72x.jpg 72w'
		], $panel->image());

		// cover enabled
		$this->assertSame([
			'back'   => 'pattern',
			'color'  => 'orange-500',
			'cover'  => true,
			'icon'   => 'image',
			'url'    => '/media/site/' . $hash . '/test.jpg',
			'src'    => Model::imagePlaceholder(),
			'srcset' => '/media/site/' . $hash . '/test-36x36-crop.jpg 1x, /media/site/' . $hash . '/test-72x72-crop.jpg 2x'
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
		$panel = $this->panel();
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

		$model = new ModelPage(['slug'  => 'foo']);
		$panel = new File($page->file('test.jpg'));
		$data  = $panel->pickerData(['model' => $model]);

		$this->assertSame('(image: file://test-file)', $data['dragText']);
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
}
