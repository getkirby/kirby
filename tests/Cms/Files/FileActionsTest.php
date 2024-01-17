<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;
use Kirby\Image\Image;

class FileActionsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/files';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.FileActions';

	protected $app;

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

	/**
	 * @dataProvider fileProvider
	 */
	public function testChangeName(File $file)
	{
		// create an empty dummy file
		F::write($file->root(), '');
		// ...and an empty content file for it
		F::write($file->storage()->contentFile('published', 'default'), '');

		$this->assertFileExists($file->root());
		$this->assertFileExists($file->storage()->contentFile('published', 'default'));

		$result = $file->changeName('test');

		$this->assertNotSame($file->root(), $result->root());
		$this->assertSame('test.csv', $result->filename());
		$this->assertFileExists($result->root());
		$this->assertFileExists($result->storage()->contentFile('published', 'default'));
		$this->assertFileDoesNotExist($file->root());
		$this->assertFileDoesNotExist($file->storage()->contentFile('published', 'default'));
	}

	public static function fileProviderMultiLang(): array
	{
		$app = static::appWithLanguages();

		return [
			[$app->site()->file()],
			[$app->site()->children()->files()->first()]
		];
	}

	/**
	 * @dataProvider fileProviderMultiLang
	 */
	public function testChangeNameMultiLang(File $file)
	{
		$app = static::appWithLanguages();
		$app->impersonate('kirby');

		// create an empty dummy file
		F::write($file->root(), '');
		// ...and empty content files for it
		F::write($file->storage()->contentFile('published', 'en'), '');
		F::write($file->storage()->contentFile('published', 'de'), '');

		$this->assertFileExists($file->root());
		$this->assertFileExists($file->storage()->contentFile('published', 'en'));
		$this->assertFileExists($file->storage()->contentFile('published', 'de'));

		$result = $file->changeName('test');

		$this->assertNotEquals($file->root(), $result->root());
		$this->assertSame('test.csv', $result->filename());
		$this->assertFileExists($result->root());
		$this->assertFileExists($result->storage()->contentFile('published', 'en'));
		$this->assertFileExists($result->storage()->contentFile('published', 'de'));
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

		$this->assertFileExists($modified->storage()->contentFile('published', 'en'));
		$this->assertFileExists($modified->storage()->contentFile('published', 'de'));
		$this->assertFileDoesNotExist($modified->storage()->contentFile('published', 'fr'));
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testCreate($parent)
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testCreateMove($parent)
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testCreateWithDefaults($parent)
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testCreateWithDefaultsAndContent($parent)
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testCreateImage($parent)
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testCreateImageAndManipulate($parent)
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testCreateHooks($parent)
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

	/**
	 * @dataProvider fileProvider
	 */
	public function testDelete(File $file)
	{
		// create an empty dummy file
		F::write($file->root(), '');
		// ...and an empty content file for it
		F::write($file->storage()->contentFile('published', 'default'), '');

		$this->assertFileExists($file->root());
		$this->assertFileExists($file->storage()->contentFile('published', 'default'));

		$result = $file->delete();

		$this->assertTrue($result);

		$this->assertFileDoesNotExist($file->root());
		$this->assertFileDoesNotExist($file->storage()->contentFile('published', 'default'));
	}

	/**
	 * @dataProvider fileProvider
	 */
	public function testPublish($file)
	{
		// create an empty dummy file
		F::write($file->root(), '');

		$this->assertFileDoesNotExist($file->mediaRoot());

		$file->publish();

		$this->assertFileExists($file->mediaRoot());
	}

	/**
	 * @dataProvider parentProvider
	 */
	public function testReplace($parent)
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testReplaceMove($parent)
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testReplaceImage($parent)
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

	/**
	 * @dataProvider fileProvider
	 */
	public function testSave($file)
	{
		// create an empty dummy file
		F::write($file->root(), '');

		$this->assertFileExists($file->root());
		$this->assertFileDoesNotExist($file->storage()->contentFile('published', 'default'));

		$file = $file->clone(['content' => ['caption' => 'save']])->save();

		$this->assertFileExists($file->storage()->contentFile('published', 'default'));
	}

	/**
	 * @dataProvider fileProvider
	 */
	public function testUnpublish($file)
	{
		// create an empty dummy file
		F::write($file->root(), '');

		$this->assertFileDoesNotExist($file->mediaRoot());
		$file->publish();
		$this->assertFileExists($file->mediaRoot());
		$file->unpublish();
		$this->assertFileDoesNotExist($file->mediaRoot());
	}

	/**
	 * @dataProvider fileProvider
	 */
	public function testUpdate($file)
	{
		$file = $file->update([
			'caption' => $caption = 'test',
			'template' => $template = 'test'
		]);

		$this->assertSame($caption, $file->caption()->value());
		$this->assertSame($template, $file->template());
	}

	/**
	 * @dataProvider parentProvider
	 */
	public function testManipulate($parent)
	{
		$original = static::FIXTURES . '/test.jpg';

		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => $original,
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
						'filename' => 'site-1.csv'
					],
					[
						'filename' => 'site-2.csv'
					],
					[
						'filename' => 'site-3.csv'
					]
				],
			],
			'hooks' => [
				'file.changeSort:before' => function (File $file, $position) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($file);
					$phpunit->assertSame(3, $position);
					$phpunit->assertNull($file->sort()->value());
					$calls++;
				},
				'file.changeSort:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($newFile);
					$phpunit->assertIsFile($oldFile);
					$phpunit->assertSame(3, $newFile->sort()->value());
					$phpunit->assertNull($oldFile->sort()->value());
					$calls++;
				},
			]
		]);

		$app->site()->file()->changeSort(3);

		$this->assertSame(2, $calls);
	}

	/**
	 * @dataProvider parentProvider
	 */
	public function testDeleteHooks($parent)
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

	/**
	 * @dataProvider parentProvider
	 */
	public function testReplaceHooks($parent)
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
