<?php

namespace Kirby\Cms;

use Kirby\Content\VersionId;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;
use Kirby\Image\Image;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

class FileActionsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/files';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.FileActions';

	public function setUp(): void
	{
		Dir::make(static::TMP);
		$this->app = static::app();
	}

	public function tearDown(): void
	{
		Blueprint::$loaded = [];
		Dir::remove(static::TMP);
	}

	public static function app(): App
	{
		return new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							[
								'filename' => 'page.csv'
							]
						]
					]
				],
				'files' => [
					[
						'filename' => 'site.csv'
					]
				],
			],
			'users' => [
				[
					'email' => 'admin@domain.com',
					'role'  => 'admin'
				]
			],
			'user' => 'admin@domain.com'
		]);
	}

	public static function appWithLanguages()
	{
		return static::app()->clone([
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch'
				]
			]
		]);
	}

	public static function parentProvider()
	{
		$app = static::app();

		return [
			[$app->site()],
			[$app->site()->children()->first()]
		];
	}

	public static function fileProvider(): array
	{
		$app = static::app();

		return [
			[$app->site()->file()],
			[$app->site()->children()->files()->first()]
		];
	}

	#[DataProvider('fileProvider')]
	public function testChangeName(File $file)
	{
		// create an empty dummy file
		F::write($file->root(), '');
		// ...and an empty content file for it
		F::write($file->version(VersionId::latest())->contentFile('default'), '');

		$this->assertFileExists($file->root());
		$this->assertFileExists($file->version(VersionId::latest())->contentFile('default'));

		$result = $file->changeName('test');

		$this->assertNotSame($file->root(), $result->root());
		$this->assertSame('test.csv', $result->filename());
		$this->assertFileExists($result->root());
		$this->assertFileExists($result->version(VersionId::latest())->contentFile('default'));
		$this->assertFileDoesNotExist($file->root());
		$this->assertFileDoesNotExist($file->version(VersionId::latest())->contentFile('default'));
	}

	public static function fileProviderMultiLang(): array
	{
		$app = static::appWithLanguages();

		return [
			[$app->site()->file()],
			[$app->site()->children()->files()->first()]
		];
	}

	#[DataProvider('fileProviderMultiLang')]
	public function testChangeNameMultiLang(File $file)
	{
		$app = static::appWithLanguages();
		$app->impersonate('kirby');

		// create an empty dummy file
		F::write($file->root(), '');
		// ...and empty content files for it
		F::write($file->version(VersionId::latest())->contentFile('en'), '');
		F::write($file->version(VersionId::latest())->contentFile('de'), '');

		$this->assertFileExists($file->root());
		$this->assertFileExists($file->version(VersionId::latest())->contentFile('en'));
		$this->assertFileExists($file->version(VersionId::latest())->contentFile('de'));

		$result = $file->changeName('test');

		$this->assertNotEquals($file->root(), $result->root());
		$this->assertSame('test.csv', $result->filename());
		$this->assertFileExists($result->root());
		$this->assertFileExists($result->version(VersionId::latest())->contentFile('en'));
		$this->assertFileExists($result->version(VersionId::latest())->contentFile('de'));
	}

	public function testChangeTemplate()
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						[
							'type' => 'files',
							'template' => 'a'
						],
						[
							'type' => 'files',
							'template' => 'b'
						]
					]
				],
				'files/a' => [
					'title'  => 'a',
					'fields' => [
						'caption' => [
							'type' => 'text'
						],
						'text' => [
							'type' => 'textarea'
						]
					]
				],
				'files/b' => [
					'title' => 'b',
					'fields' => [
						'caption' => [
							'type' => 'info'
						],
						'text' => [
							'type' => 'textarea'
						]
					]
				],
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
						'files' => [
							[
								'filename' => 'test.jpg',
								'content'  => [
									'template' => 'a',
									'caption'  => 'Caption',
									'text'     => 'Text'
								]
							]
						]
					]
				]
			],
			'hooks' => [
				'file.changeTemplate:before' => function (File $file, $template) use ($phpunit, &$calls) {
					$phpunit->assertSame('a', $file->template());
					$phpunit->assertSame('b', $template);
					$calls++;
				},
				'file.changeTemplate:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertSame('b', $newFile->template());
					$phpunit->assertSame('a', $oldFile->template());
					$calls++;
				}
			]
		]);

		$app->impersonate('kirby');

		$file = $app->page('test')->file('test.jpg');
		$this->assertSame('a', $file->template());
		$this->assertSame('Caption', $file->caption()->value());
		$this->assertSame('Text', $file->text()->value());

		// changing to the same template
		$same = $file->changeTemplate('a');
		$this->assertSame('a', $same->template());
		$this->assertSame(0, $calls);

		// changing to another template
		$modified = $file->changeTemplate('b');
		$this->assertSame('b', $modified->template());
		$this->assertNull($modified->caption()->value());
		$this->assertSame('Text', $modified->text()->value());
		$this->assertSame(2, $calls);

		$this->assertSame($modified, $app->page('test')->file('test.jpg'));
	}

	public function testChangeTemplateMultilang()
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						[
							'type' => 'files',
							'template' => 'a'
						],
						[
							'type' => 'files',
							'template' => 'b'
						]
					]
				],
				'files/a' => [
					'title' => 'a',
					'fields' => [
						'caption' => [
							'type' => 'text'
						],
						'text' => [
							'type' => 'textarea'
						]
					]
				],
				'files/b' => [
					'title' => 'b',
					'fields' => [
						'caption' => [
							'type' => 'info'
						],
						'text' => [
							'type' => 'textarea'
						]
					]
				],
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
						'files' => [
							[
								'filename' => 'test.jpg',
								'translations' => [
									[
										'code' => 'en',
										'content' => [
											'template' => 'a',
											'caption'  => 'This is the caption',
											'text'     => 'This is the text'
										]
									],
									[
										'code' => 'de',
										'content' => [
											'caption' => 'Das ist die Caption',
											'text'    => 'Das ist der Text'
										]
									],
									[
										'code' => 'fr'
									]
								],
							]
						]
					]
				]
			],
			'hooks' => [
				'file.changeTemplate:before' => function (File $file, $template) use ($phpunit, &$calls) {
					$phpunit->assertSame('a', $file->template());
					$phpunit->assertSame('b', $template);
					$calls++;
				},
				'file.changeTemplate:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertSame('b', $newFile->template());
					$phpunit->assertSame('a', $oldFile->template());
					$calls++;
				}
			],
			'languages' => [
				[
					'code' => 'en',
					'name' => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
				],
				[
					'code' => 'fr',
					'name' => 'Français',
				]
			]
		]);

		$app->impersonate('kirby');

		$file = $app->page('test')->file('test.jpg');
		$this->assertSame('a', $file->template());
		$this->assertSame('This is the text', $file->text()->value());
		$this->assertSame('This is the caption', $file->caption()->value());

		$modified = $file->changeTemplate('b');

		$this->assertSame('b', $modified->template());
		$this->assertNull($modified->caption()->value());
		$this->assertSame('This is the text', $modified->text()->value());
		$this->assertSame(2, $calls);

		$modified->purge();
		$app->setCurrentLanguage('de');
		$this->assertNull($modified->caption()->value());
		$this->assertSame('Das ist der Text', $modified->text()->value());

		$this->assertFileExists($modified->version(VersionId::latest())->contentFile('en'));
		$this->assertFileExists($modified->version(VersionId::latest())->contentFile('de'));
		$this->assertFileDoesNotExist($modified->version(VersionId::latest())->contentFile('fr'));
	}

	public function testChangeTemplateDefault()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'pages/test-default' => [
					'sections' => [
						[
							'type' => 'files',
						],
						[
							'type' => 'files',
							'template' => 'for-default-b'
						]
					]
				],
				'files/for-default-b' => [
					'title' => 'Alternative B'
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test-default',
						'files' => [
							[
								'filename' => 'test.jpg',
								'content'  => ['template' => 'for-default-a']
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$file = $app->page('test')->file('test.jpg');
		$this->assertSame('for-default-a', $file->template());
		$this->assertSame('for-default-a', $file->content()->get('template')->value());

		$modified = $file->changeTemplate('default');
		$this->assertSame('default', $modified->template());
		$this->assertNull($modified->content()->get('template')->value());

		$back = $modified->changeTemplate('for-default-b');
		$this->assertSame('for-default-b', $back->template());
		$this->assertSame('for-default-b', $back->content()->get('template')->value());

		$modified = $file->changeTemplate(null);
		$this->assertSame('default', $modified->template());
		$this->assertNull($modified->content()->get('template')->value());
	}

	public function testChangeTemplateInvalidAccept()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'pages/test-default' => [
					'sections' => [
						[
							'type' => 'files',
							'template' => 'for-default-b'
						],
						[
							'type' => 'files',
							'template' => 'for-default-c'
						],
						[
							'type' => 'files',
							'template' => 'for-default-d'
						]
					]
				],
				'files/for-default-b' => [
					'title'  => 'Alternative B',
					'accept' => 'image'
				],
				'files/for-default-c' => [
					'title'  => 'Alternative C'
				],
				'files/for-default-d' => [
					'title'  => 'Alternative D'
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test-default',
						'files' => [
							[
								'filename' => 'test.pdf',
								'content'  => ['template' => 'for-default-a']
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The template for the file "test/test.pdf" cannot be changed to "for-default-b" (valid: "for-default-c, for-default-d")');

		$file = $app->page('test')->file('test.pdf');
		$file->changeTemplate('for-default-b');
	}

	public function testCommit(): void
	{
		$phpunit = $this;
		$page    = new Page(['slug' => 'text']);

		$app = $this->app->clone([
			'hooks' => [
				'file.changeSort:before' => [
					function (File $file, int $position) use ($phpunit, $page) {
						$phpunit->assertSame(99, $position);
						$phpunit->assertSame(1, $file->sort()->value());
						// altering $file which will be passed
						// to subsequent hook
						return new File([
							'filename' => 'test.jpg',
							'parent'   => $page,
							'content'  => ['sort' => 2]
						]);
					},
					function (File $file, int $position) use ($phpunit, $page) {
						$phpunit->assertSame(99, $position);
						// altered $file from previous hook
						$phpunit->assertSame(2, $file->sort()->value());
						// altering $file which will be used
						// in the commit callback closure
						return new File([
							'filename' => 'test.jpg',
							'parent'   => $page,
							'content'  => ['sort' => 3]
						]);
					}
				],
				'file.changeSort:after' => [
					function (File $newFile, File $oldFile) use ($phpunit, $page) {
						$phpunit->assertSame(1, $oldFile->sort()->value());
						// modified $file from the commit callback closure
						$phpunit->assertSame(99, $newFile->sort()->value());
						// altering $newFile which will be passed
						// to subsequent hook
						return new File([
							'filename' => 'test.jpg',
							'parent'   => $page,
							'content'  => ['sort' => 4]
						]);
					},
					function (File $newFile, File $oldFile) use ($phpunit, $page) {
						$phpunit->assertSame(1, $oldFile->sort()->value());
						// altered $newFile from previous hook
						$phpunit->assertSame(4, $newFile->sort()->value());
						// altering $newFile which will be the final result
						return new File([
							'filename' => 'test.jpg',
							'parent'   => $page,
							'content'  => ['sort' => 5]
						]);
					}
				]
			]
		]);

		$app->impersonate('kirby');

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => ['sort' => 1]
		]);
		$class  = new ReflectionClass($file);
		$commit = $class->getMethod('commit');
		$result = $commit->invokeArgs($file, [
			'changeSort',
			['file' => $file, 'position' => 99],
			function (File $file, int $position) use ($phpunit, $page) {
				$phpunit->assertSame(99, $position);
				// altered $page from before hooks
				$phpunit->assertSame(3, $file->sort()->value());
				return new File([
					'filename' => 'test.jpg',
					'parent'   => $page,
					'content'  => ['sort' => $position]
				]);
			}
		]);

		// altered result from last after hook
		$this->assertSame(5, $result->sort()->value());
	}

	public function testChangeTemplateManipulate()
	{
		$testImage = static::FIXTURES . '/test.jpg';

		$app = $this->app->clone([
			'blueprints' => [
				'pages/test-default' => [
					'sections' => [
						[
							'type' => 'files',
							'template' => 'manipulate-a'
						],
						[
							'type' => 'files',
							'template' => 'manipulate-b'
						]
					]
				],
				'files/manipulate-a' => [
					'title'  => 'Manipulate A',
				],
				'files/manipulate-b' => [
					'title'  => 'Manipulate B',
					'create' => [
						'width'  => 100,
						'height' => 100,
						'format' => 'webp'
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test-default',
						'files' => [
							[
								'filename' => 'test.jpg',
								'content'  => ['template' => 'manipulate-a']
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');
		$page = $app->page('test');

		F::copy($testImage, $page->root() . '/test.jpg');
		F::write($page->root() . '/test.jpg.txt', 'Template: manipulate-a');

		$file = $page->file('test.jpg');
		$this->assertSame('jpg', $file->extension());
		$this->assertSame(128, $file->width());
		$this->assertSame(128, $file->height());

		$file = $file->changeTemplate('manipulate-b');
		$this->assertSame('webp', $file->extension());
		$this->assertSame(100, $file->width());
		$this->assertSame(100, $file->height());
	}

	public function testChangeTemplateManipulateNonImage()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'pages/test-default' => [
					'sections' => [
						[
							'type' => 'files',
							'template' => 'manipulate-a'
						],
						[
							'type' => 'files',
							'template' => 'manipulate-b'
						]
					]
				],
				'files/manipulate-a' => [
					'title'  => 'Manipulate A',
				],
				'files/manipulate-b' => [
					'title'  => 'Manipulate B',
					'create' => [
						'width'  => 100,
						'height' => 100,
						'format' => 'webp'
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test-default',
						'files' => [
							[
								'filename' => 'test.pdf',
								'content'  => ['template' => 'manipulate-a']
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$file = $app->page('test')->file('test.pdf');
		$newFile = $file->changeTemplate('manipulate-b');

		$this->assertSame('pdf', $file->extension());
		$this->assertSame('pdf', $newFile->extension());
	}

	public function testCopyRenewUuid()
	{
		// create dumy file
		F::write($source = static::TMP . '/original.md', '# Foo');

		$file = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => new Page(['slug' => 'test'])
		]);

		$oldUuid = $file->content()->get('uuid')->value();
		$this->assertIsString($oldUuid);

		$destination = new Page([
			'slug' => 'newly',
			'root' => static::TMP . '/new-page'
		]);

		$copy = $file->copy($destination);

		$newUuid = $copy->content()->get('uuid')->value();
		$this->assertIsString($newUuid);
		$this->assertNotSame($oldUuid, $newUuid);
	}

	#[DataProvider('parentProvider')]
	public function testCreate(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$this->assertFileExists($source);
		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.md');
		$this->assertInstanceOf(BaseFile::class, $result->asset());

		// make sure file received UUID right away
		$this->assertIsString($result->content()->get('uuid')->value());
	}

	#[DataProvider('parentProvider')]
	public function testCreateDuplicate(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$uuid = $result->content()->get('uuid')->value();

		$duplicate = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$this->assertSame($uuid, $duplicate->content()->get('uuid')->value());
	}

	#[DataProvider('parentProvider')]
	public function testCreateMove(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		], true);

		$this->assertFileDoesNotExist($source);
		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.md');
		$this->assertInstanceOf(BaseFile::class, $result->asset());

		// make sure file received UUID right away
		$this->assertIsString($result->content()->get('uuid')->value());
	}

	#[DataProvider('parentProvider')]
	public function testCreateWithDefaults(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent,
			'blueprint' => [
				'name' => 'test',
				'fields' => [
					'a'  => [
						'type'    => 'text',
						'default' => 'A'
					],
					'b' => [
						'type'    => 'textarea',
						'default' => 'B'
					]
				]
			]
		]);

		$this->assertSame('A', $result->a()->value());
		$this->assertSame('B', $result->b()->value());
	}

	#[DataProvider('parentProvider')]
	public function testCreateWithDefaultsAndContent(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'content' => [
				'a' => 'Custom A'
			],
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent,
			'blueprint' => [
				'name' => 'test',
				'fields' => [
					'a'  => [
						'type'    => 'text',
						'default' => 'A'
					],
					'b' => [
						'type'    => 'textarea',
						'default' => 'B'
					]
				]
			]
		]);

		$this->assertSame('Custom A', $result->a()->value());
		$this->assertSame('B', $result->b()->value());
	}

	#[DataProvider('parentProvider')]
	public function testCreateImage(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$source = static::FIXTURES . '/test.jpg';

		$result = File::create([
			'filename' => 'test.jpg',
			'source'   => $source,
			'parent'   => $parent
		]);

		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.jpg');
		$this->assertInstanceOf(Image::class, $result->asset());
	}

	#[DataProvider('parentProvider')]
	public function testCreateImageAndManipulate(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$source = static::FIXTURES . '/test.jpg';
		$result = File::create([
			'filename' => 'test.jpg',
			'source'   => $source,
			'parent'   => $parent,
			'blueprint' => [
				'name' => 'test',
				'create' => [
					'width'  => 100,
					'height' => 100,
					'format' => 'webp'
				]
			]
		]);

		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.webp');
		$this->assertSame(100, $result->width());
		$this->assertSame(100, $result->height());
		$this->assertSame('webp', $result->extension());
		$this->assertSame('test.webp', $result->filename());
	}

	#[DataProvider('parentProvider')]
	public function testCreateManipulateNonImage(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$source = static::FIXTURES . '/test.pdf';

		$result = File::create([
			'filename'  => 'test.pdf',
			'source'    => $source,
			'parent'    => $parent,
			'blueprint' => [
				'name'   => 'test',
				'create' => [
					'width'  => 100,
					'height' => 100,
					'format' => 'webp'
				]
			]
		]);

		$this->assertFileEquals($source, $result->root());
	}

	#[DataProvider('parentProvider')]
	public function testCreateHooks(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$phpunit = $this;
		$before  = false;
		$after   = false;

		$app = $this->app->clone([
			'hooks' => [
				'file.create:before' => function (File $file, BaseFile $upload) use (&$before) {
					$before = true;
				},
				'file.create:after' => function (File $file) use (&$after, $phpunit) {
					$phpunit->assertTrue($file->siblings(true)->has($file));
					$phpunit->assertTrue($file->parent()->files()->has($file));
					$phpunit->assertSame('test.md', $file->filename());

					$after = true;
				}
			]
		]);

		// create the dummy source
		F::write($source = static::TMP . '/source.md', '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$this->assertTrue($before);
		$this->assertTrue($after);
	}

	#[DataProvider('fileProvider')]
	public function testDelete(File $file)
	{
		// create an empty dummy file
		F::write($file->root(), '');
		// ...and an empty content file for it
		F::write($file->version(VersionId::latest())->contentFile('default'), '');

		$this->assertFileExists($file->root());
		$this->assertFileExists($file->version(VersionId::latest())->contentFile('default'));

		$result = $file->delete();

		$this->assertTrue($result);

		$this->assertFileDoesNotExist($file->root());
		$this->assertFileDoesNotExist($file->version(VersionId::latest())->contentFile('default'));
	}

	#[DataProvider('fileProvider')]
	public function testPublish(\Kirby\Cms\File|null $file)
	{
		// create an empty dummy file
		F::write($file->root(), '');

		$this->assertFileDoesNotExist($file->mediaRoot());

		$file->publish();

		$this->assertFileExists($file->mediaRoot());
	}

	#[DataProvider('parentProvider')]
	public function testReplace(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$original    = static::TMP . '/original.md';
		$replacement = static::TMP . '/replacement.md';

		// create the dummy files
		F::write($original, '# Original');
		F::write($replacement, '# Replacement');

		$originalFile = File::create([
			'filename' => 'test.md',
			'source'   => $original,
			'parent'   => $parent
		]);

		$this->assertFileExists($original);
		$this->assertSame(F::read($original), F::read($originalFile->root()));
		$this->assertInstanceOf(BaseFile::class, $originalFile->asset());

		$replacedFile = $originalFile->replace($replacement);

		$this->assertFileExists($original);
		$this->assertFileExists($replacement);
		$this->assertSame(F::read($replacement), F::read($replacedFile->root()));
		$this->assertInstanceOf(BaseFile::class, $replacedFile->asset());
	}

	#[DataProvider('parentProvider')]
	public function testReplaceMove(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$original    = static::TMP . '/original.md';
		$replacement = static::TMP . '/replacement.md';

		// create the dummy files
		F::write($original, '# Original');
		F::write($replacement, '# Replacement');

		$originalFile = File::create([
			'filename' => 'test.md',
			'source'   => $original,
			'parent'   => $parent
		]);

		$this->assertFileExists($original);
		$this->assertSame(F::read($original), F::read($originalFile->root()));
		$this->assertInstanceOf(BaseFile::class, $originalFile->asset());

		$replacedFile = $originalFile->replace($replacement, true);

		$this->assertFileExists($original);
		$this->assertFileDoesNotExist($replacement);
		$this->assertSame('# Replacement', F::read($replacedFile->root()));
		$this->assertInstanceOf(BaseFile::class, $replacedFile->asset());
	}

	#[DataProvider('parentProvider')]
	public function testReplaceImage(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$original    = static::FIXTURES . '/test.jpg';
		$replacement = static::FIXTURES . '/cat.jpg';

		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => $original,
			'parent'   => $parent
		]);

		$this->assertSame(F::read($original), F::read($originalFile->root()));
		$this->assertInstanceOf(Image::class, $originalFile->asset());

		$replacedFile = $originalFile->replace($replacement);

		$this->assertSame(F::read($replacement), F::read($replacedFile->root()));
		$this->assertInstanceOf(Image::class, $replacedFile->asset());
	}

	#[DataProvider('parentProvider')]
	public function testReplaceManipulateNonImage(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$original    = static::FIXTURES . '/test.pdf';
		$replacement = static::FIXTURES . '/doc.pdf';

		$originalFile = File::create([
			'filename' => 'test.pdf',
			'source'   => $original,
			'parent'   => $parent,
			'blueprint' => [
				'name' => 'test',
				'create' => [
					'width'  => 100,
					'height' => 100,
					'format' => 'webp'
				]
			]
		]);

		$this->assertFileEquals($original, $originalFile->root());

		$replacedFile = $originalFile->replace($replacement);
		$this->assertFileEquals($replacement, $replacedFile->root());
		$this->assertSame('pdf', $replacedFile->extension());
	}

	#[DataProvider('fileProvider')]
	public function testSave(\Kirby\Cms\File|null $file)
	{
		// create an empty dummy file
		F::write($file->root(), '');

		$this->assertFileExists($file->root());
		$this->assertFileDoesNotExist($file->version(VersionId::latest())->contentFile('default'));

		$file = $file->clone(['content' => ['caption' => 'save']])->save();

		$this->assertFileExists($file->version(VersionId::latest())->contentFile('default'));
	}

	#[DataProvider('fileProvider')]
	public function testUnpublish(\Kirby\Cms\File|null $file)
	{
		// create an empty dummy file
		F::write($file->root(), '');

		$this->assertFileDoesNotExist($file->mediaRoot());
		$file->publish();
		$this->assertFileExists($file->mediaRoot());
		$file->unpublish();
		$this->assertFileDoesNotExist($file->mediaRoot());
	}

	#[DataProvider('fileProvider')]
	public function testUpdate(\Kirby\Cms\File|null $file)
	{
		$file = $file->update([
			'caption' => $caption = 'test',
			'template' => $template = 'test'
		]);

		$this->assertSame($caption, $file->caption()->value());
		$this->assertSame($template, $file->template());
	}

	#[DataProvider('parentProvider')]
	public function testManipulate(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		]);

		$this->assertSame(128, $originalFile->width());
		$this->assertSame(128, $originalFile->height());

		$replacedFile = $originalFile->manipulate([
			'width' => 100,
			'height' => 100,
		]);

		$this->assertSame($originalFile->root(), $replacedFile->root());
		$this->assertSame(100, $replacedFile->width());
		$this->assertSame(100, $replacedFile->height());
	}

	#[DataProvider('parentProvider')]
	public function testManipulateNonImage(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$originalFile = File::create([
			'filename' => 'test.mp4',
			'source'   => static::FIXTURES . '/test.mp4',
			'parent'   => $parent
		]);

		$replacedFile = $originalFile->manipulate([
			'width' => 100,
			'height' => 100,
		]);

		// proves strictly that both are the same object
		$this->assertSame($originalFile, $replacedFile);
	}

	#[DataProvider('parentProvider')]
	public function testManipulateValidFormat(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		]);

		$this->assertSame(128, $originalFile->width());
		$this->assertSame(128, $originalFile->height());

		$replacedFile = $originalFile->manipulate([
			'width'  => 100,
			'height' => 100,
			'format' => 'webp'
		]);

		$this->assertSame('webp', $replacedFile->extension());
		$this->assertSame(100, $replacedFile->width());
		$this->assertSame(100, $replacedFile->height());
	}

	#[DataProvider('parentProvider')]
	public function testManipulateInvalidValidFormat(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$originalFile = File::create([
			'filename' => 'test.mp4',
			'source'   => static::FIXTURES . '/test.mp4',
			'parent'   => $parent
		]);

		$replacedFile = $originalFile->manipulate([
			'width'  => 100,
			'height' => 100,
			'format' => 'webp'
		]);

		// proves strictly that both are the same object
		$this->assertSame($originalFile, $replacedFile);
		$this->assertSame('mp4', $replacedFile->extension());
	}

	public function testChangeNameHooks()
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'file.changeName:before' => function (File $file, $name) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($file);
					$phpunit->assertSame('test', $name);
					$phpunit->assertSame('site.csv', $file->filename());
					$calls++;
				},
				'file.changeName:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($newFile);
					$phpunit->assertIsFile($oldFile);
					$phpunit->assertSame('test.csv', $newFile->filename());
					$phpunit->assertSame('site.csv', $oldFile->filename());
					$calls++;
				},
			]
		]);

		$app->site()->file()->changeName('test');

		$this->assertSame(2, $calls);
	}

	public function testChangeSortHooks()
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'site-1.csv',
						'content'  => ['sort' => 1]
					],
					[
						'filename' => 'site-2.csv',
						'content'  => ['sort' => 2]
					],
					[
						'filename' => 'site-3.csv',
						'content'  => ['sort' => 3]
					]
				],
			],
			'hooks' => [
				'file.changeSort:before' => function (File $file, $position) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($file);
					$phpunit->assertSame(3, $position);
					$phpunit->assertSame(1, $file->sort()->value());
					$calls++;
				},
				'file.changeSort:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($newFile);
					$phpunit->assertIsFile($oldFile);
					$phpunit->assertSame(3, $newFile->sort()->value());
					$phpunit->assertSame(1, $oldFile->sort()->value());
					$calls++;
				},
			]
		]);

		$app->site()->file()->changeSort(1);
		$this->assertSame(0, $calls);

		$app->site()->file()->changeSort(3);
		$this->assertSame(2, $calls);
	}

	#[DataProvider('parentProvider')]
	public function testDeleteHooks(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$calls = 0;
		$phpunit = $this;

		$this->app->clone([
			'hooks' => [
				'file.delete:before' => function (File $file) use ($phpunit, &$calls) {
					$phpunit->assertFileExists($file->root());
					$phpunit->assertSame('test.md', $file->filename());
					$calls++;
				},
				'file.delete:after' => function ($status, File $file) use ($phpunit, &$calls) {
					$phpunit->assertTrue($status);
					$phpunit->assertFileDoesNotExist($file->root());
					$phpunit->assertSame('test.md', $file->filename());
					$calls++;
				}
			]
		]);

		// create the dummy source
		F::write($source = static::TMP . '/source.md', '# Test');

		$file = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$file->delete();

		$this->assertSame(2, $calls);
	}

	#[DataProvider('parentProvider')]
	public function testReplaceHooks(\Kirby\Cms\Site|\Kirby\Cms\Page $parent)
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'file.replace:before' => function (File $file, BaseFile $upload) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($file);
					$phpunit->assertInstanceOf(BaseFile::class, $upload);
					$phpunit->assertSame('site.csv', $file->filename());
					$phpunit->assertSame('replace.csv', $upload->filename());
					$phpunit->assertFileDoesNotExist($file->root());
					$calls++;
				},
				'file.replace:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($newFile);
					$phpunit->assertIsFile($oldFile);
					$phpunit->assertSame('site.csv', $newFile->filename());
					$phpunit->assertSame('Replace', F::read($newFile->root()));
					$phpunit->assertSame('site.csv', $oldFile->filename());
					$calls++;
				},
			]
		]);

		// create the dummy source
		F::write($source = static::TMP . '/replace.csv', 'Replace');

		File::create([
			'filename' => 'replace.csv',
			'source'   => $source,
			'parent'   => $parent
		]);

		$app->site()->file()->replace($source);

		$this->assertSame(2, $calls);
	}

	public function testUpdateHooks()
	{
		$calls = 0;
		$phpunit = $this;
		$input = [
			'title' => 'Test'
		];

		$app = $this->app->clone([
			'hooks' => [
				'file.update:before' => function (File $file, $values, $strings) use ($phpunit, $input, &$calls) {
					$phpunit->assertIsFile($file);
					$phpunit->assertNull($file->title()->value());
					$phpunit->assertSame($input, $values);
					$phpunit->assertSame($input, $strings);
					$calls++;
				},
				'file.update:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($newFile);
					$phpunit->assertIsFile($oldFile);
					$phpunit->assertSame('Test', $newFile->title()->value());
					$phpunit->assertNull($oldFile->title()->value());
					$calls++;
				},
			]
		]);

		$app->site()->file()->update($input);

		$this->assertSame(2, $calls);
	}
}
