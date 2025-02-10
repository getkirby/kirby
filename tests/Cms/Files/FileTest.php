<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;
use Kirby\Panel\File as Panel;
use PHPUnit\Framework\Attributes\CoversClass;

class FileTestModel extends File
{
}

#[CoversClass(File::class)]
class FileTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.File';

	protected function defaults(App|null $kirby = null): array
	{
		$page = new Page([
			'kirby' => $kirby,
			'slug'  => 'test'
		]);

		return [
			'filename' => 'cover.jpg',
			'parent'   => $page,
			'url'      => 'https://getkirby.com/projects/project-a/cover.jpg'
		];
	}

	protected function file(array $props = [])
	{
		$defaults = $this->defaults($props['kirby'] ?? null);
		return new File([...$defaults, ...$props]);
	}

	public function testAsset()
	{
		$file = $this->file();
		$this->assertInstanceOf(BaseFile::class, $file->asset());
		$this->assertSame(
			'https://getkirby.com/projects/project-a/cover.jpg',
			$file->asset()->url()
		);
	}

	public function testBlueprints()
	{
		$app = new App([
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						[
							'type' => 'files',
							'template' => 'for-section/a'
						],
						[
							'type' => 'files',
							'template' => 'for-section/b'
						],
						[
							'type' => 'files',
							'template' => 'not-exist'
						],
						[
							'type' => 'fields',
							'fields' => [
								'a' => [
									'type' => 'info'
								],
								'b' => [
									'type' => 'files'
								],
								'c' => [
									'type'    => 'files',
									'uploads' => 'for-fields/a'
								],
								'd' => [
									'type'    => 'files',
									'uploads' => [
										'template' => 'for-fields/b'
									]
								],
								'e' => [
									'type'    => 'files',
									'uploads' => [
										'parent'   => 'foo',
										'template' => 'for-fields/c'
									]
								],
								'f' => [
									'type'    => 'files',
									'uploads' => 'for-fields/c'
								],
								'g' => [
									'type'    => 'textarea',
									'uploads' => 'for-fields/d'
								],
								'h' => [
									'type'    => 'structure',
									'fields'  => [
										[
											'type'    => 'files',
											'uploads' => 'for-fields/e'
										],
										[
											'type'    => 'structure',
											'fields'  => [
												[
													'type'    => 'files',
													'uploads' => 'for-fields/f'
												]
											]
										]
									]
								],
							]
						]
					]
				],
				'files/for-section/a' => [
					'title' => 'Type A'
				],
				'files/for-section/b' => [
					'title' => 'Type B'
				],
				'files/for-fields/a' => [
					'title' => 'Field Type A'
				],
				'files/for-fields/b' => [
					'title' => 'Field Type B'
				],
				'files/for-fields/c' => [
					'title' => 'Field Type C',
					'accept' => 'image'
				],
				'files/for-fields/d' => [
					'title' => 'Field Type D'
				],
				'files/for-fields/e' => [
					'title' => 'Field Type E'
				],
				'files/for-fields/f' => [
					'title' => 'Field Type F'
				],
				'files/current' => [
					'title' => 'Just the current'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
						'files'    => [
							[
								'filename' => 'test.pdf',
								'content'  => ['template' => 'current']
							]
						]
					]
				]
			]
		]);

		$file       = $app->file('test/test.pdf');
		$blueprints = $file->blueprints();
		$this->assertCount(9, $blueprints);
		$this->assertSame('default', $blueprints[0]['name']);
		$this->assertSame('for-fields/a', $blueprints[1]['name']);
		$this->assertSame('for-fields/b', $blueprints[2]['name']);
		$this->assertSame('for-fields/d', $blueprints[3]['name']);
		$this->assertSame('for-fields/e', $blueprints[4]['name']);
		$this->assertSame('for-fields/f', $blueprints[5]['name']);
		$this->assertSame('current', $blueprints[6]['name']);
		$this->assertSame('for-section/a', $blueprints[7]['name']);
		$this->assertSame('for-section/b', $blueprints[8]['name']);
	}

	public function testBlueprintsInSection()
	{
		$app = new App([
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						'section-a' => [
							'type' => 'files',
							'template' => 'for-section/a'
						],
						'section-b' => [
							'type' => 'files',
							'template' => 'for-section/b'
						],
						'section-c' => [
							'type' => 'fields',
							'fields' => [
								[
									'type' => 'files'
								],
								[
									'type'    => 'files',
									'uploads' => 'for-fields/a'
								],
								[
									'type'    => 'files',
									'uploads' => [
										'template' => 'for-fields/b'
									]
								],
								[
									'type'    => 'files',
									'uploads' => [
										'parent'   => 'foo',
										'template' => 'for-fields/c'
									]
								],
								[
									'type'    => 'files',
									'uploads' => 'for-fields/c'
								]
							]
						]
					]
				],
				'files/for-section/a' => [
					'title' => 'Type A'
				],
				'files/for-section/b' => [
					'title' => 'Type B'
				],
				'files/for-fields/a' => [
					'title' => 'Field Type A'
				],
				'files/for-fields/b' => [
					'title' => 'Field Type B'
				],
				'files/for-fields/c' => [
					'title' => 'Field Type C',
					'accept' => 'image'
				],
				'files/current' => [
					'title' => 'Just the current'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
						'files'    => [
							[
								'filename' => 'test.pdf',
								'content'  => ['template' => 'current']
							]
						]
					]
				]
			]
		]);

		$file       = $app->file('test/test.pdf');
		$blueprints = $file->blueprints('section-a');
		$this->assertCount(2, $blueprints);
		$this->assertSame('current', $blueprints[0]['name']);
		$this->assertSame('for-section/a', $blueprints[1]['name']);

		$blueprints = $file->blueprints('section-c');
		$this->assertCount(4, $blueprints);
		$this->assertSame('default', $blueprints[0]['name']);
		$this->assertSame('for-fields/a', $blueprints[1]['name']);
		$this->assertSame('for-fields/b', $blueprints[2]['name']);
		$this->assertSame('current', $blueprints[3]['name']);
	}

	public function testContent()
	{
		$file = $this->file([
			'content' => [
				'test' => 'Test'
			]
		]);

		$this->assertSame('Test', $file->content()->get('test')->value());
	}

	public function testDefaultContent()
	{
		$file = $this->file();

		$this->assertInstanceOf(Content::class, $file->content());
	}

	public function testFilename()
	{
		$this->assertSame($this->defaults()['filename'], $this->file()->filename());
	}

	public function testContentFileData()
	{
		$file = $this->file();

		$this->assertSame([], $file->contentFileData([]));
		$this->assertSame(['foo' => 'bar'], $file->contentFileData(['foo' => 'bar']));

		$file = $this->file(['content' => ['template' => 'image']]);
		$this->assertSame(['template' => 'image'], $file->contentFileData([]));
		$this->assertSame(['foo' => 'bar', 'template' => 'image'], $file->contentFileData(['foo' => 'bar']));
		$this->assertSame(['template' => null], $file->contentFileData(['template' => null]));
	}

	public function testPage()
	{
		$file = $this->file([
			'parent' => $page = new Page(['slug' => 'test'])
		]);

		$this->assertIsPage($page, $file->page());

		$file = $this->file([
			'parent' => new User([])
		]);

		$this->assertNull($file->page());
	}

	public function testParentId()
	{
		$file = $this->file([
			'parent' => $page = new Page(['slug' => 'test'])
		]);

		$this->assertSame('test', $file->parentId());
	}

	public function testHtml()
	{
		$file = $this->file([
			'filename' => 'test.jpg',
			'url'      => 'http://getkirby.com/test.jpg',
			'parent'   => new Site(),
			'content' => [
				'alt' => 'This is the alt text'
			]
		]);
		$this->assertSame('<img alt="This is the alt text" src="http://getkirby.com/test.jpg">', $file->html());
	}

	public function testUrl()
	{
		$this->assertSame($this->defaults()['url'], $this->file()->url());
	}

	public function testToString()
	{
		$file = $this->file(['filename' => 'super.jpg']);
		$this->assertSame('super.jpg', $file->toString('{{ file.filename }}'));
	}

	public function testIsReadable()
	{
		$app = new App([
			'blueprints' => [
				'files/foo' => [
					'options' => ['read' => false]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'id'    => 'admin',
					'role'  => 'admin'
				]
			],
			'user' => 'admin'
		]);

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg'
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isReadable()); // test caching

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg',
			'template' => 'foo'
		]);
		$this->assertFalse($file->isReadable());
		$this->assertFalse($file->isReadable()); // test caching
	}

	public function testIsAccessible()
	{
		$app = new App([
			'blueprints' => [
				'files/bar' => [
					'options' => ['access' => false]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'id'    => 'admin',
					'role'  => 'admin'
				]
			],
			'user' => 'admin'
		]);

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg'
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertTrue($file->isListable());

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg',
			'template' => 'bar'
		]);
		$this->assertTrue($file->isReadable());
		$this->assertFalse($file->isAccessible());
		$this->assertFalse($file->isListable());
	}

	public function testIsAccessibleRead()
	{
		$app = new App([
			'blueprints' => [
				'files/bar-read' => [
					'options' => ['read' => false, 'access' => true]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'id'    => 'admin',
					'role'  => 'admin'
				]
			],
			'user' => 'admin'
		]);

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg'
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertTrue($file->isListable());

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg',
			'template' => 'bar-read'
		]);
		$this->assertFalse($file->isReadable());
		$this->assertFalse($file->isAccessible());
		$this->assertFalse($file->isListable());
	}

	public function testIsListable()
	{
		$app = new App([
			'blueprints' => [
				'files/baz' => [
					'options' => ['list' => false]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'id'    => 'admin',
					'role'  => 'admin'
				]
			],
			'user' => 'admin'
		]);

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg'
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertTrue($file->isListable());

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg',
			'template' => 'baz'
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertFalse($file->isListable());
	}

	public function testIsListableRead()
	{
		$app = new App([
			'blueprints' => [
				'files/baz-read' => [
					'options' => ['read' => false, 'list' => true]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'id'    => 'admin',
					'role'  => 'admin'
				]
			],
			'user' => 'admin'
		]);

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg'
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertTrue($file->isListable());

		$file = $this->file([
			'kirby'    => $app,
			'filename' => 'test.jpg',
			'template' => 'baz-read'
		]);
		$this->assertFalse($file->isReadable());
		$this->assertFalse($file->isAccessible());
		$this->assertFalse($file->isListable());
	}

	public function testMediaHash()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			],
			'options' => [
				'content.salt' => 'test'
			]
		]);

		F::write(static::TMP . '/test.jpg', 'test');
		touch(static::TMP . '/test.jpg', 5432112345);
		$file = $this->file([
			'kirby'    => $app,
			'parent'   => $app->site(),
			'filename' => 'test.jpg'
		]);

		$this->assertSame('08756f3115-5432112345', $file->mediaHash());
	}

	public function testMediaToken()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			],
			'options' => [
				'content.salt' => 'test'
			]
		]);

		$file = $this->file([
			'kirby'    => $app,
			'parent'   => $app->site(),
			'filename' => 'test.jpg'
		]);

		$this->assertSame('08756f3115', $file->mediaToken());
	}

	public function testModified()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			]
		]);

		// create a file
		F::write($file = static::TMP . '/test.js', 'test');

		$modified = filemtime($file);
		$file     = $app->file('test.js');

		$this->assertSame($modified, $file->modified());

		// default date handler
		$format = 'd.m.Y';
		$this->assertSame(date($format, $modified), $file->modified($format));

		// custom date handler
		$format = '%d.%m.%Y';
		$this->assertSame(@strftime($format, $modified), $file->modified($format, 'strftime'));
	}

	public function testModifiedContent()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			]
		]);

		// create a file
		F::write($file = static::TMP . '/test.js', 'test');
		touch($file, $modifiedFile = \time() + 2);

		F::write($content = static::TMP . '/test.js.txt', 'test');
		touch($file, $modifiedContent = \time() + 5);

		$file = $app->file('test.js');

		$this->assertNotEquals($modifiedFile, $file->modified());
		$this->assertSame($modifiedContent, $file->modified());
	}

	public function testModifiedSpecifyingLanguage()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			]
		]);

		// create a file
		F::write(static::TMP . '/test.js', 'test');

		// create the english content
		F::write($file = static::TMP . '/test.js.en.txt', 'test');
		touch($file, $modifiedEnContent = \time() + 2);

		// create the german content
		F::write($file = static::TMP . '/test.js.de.txt', 'test');
		touch($file, $modifiedDeContent = \time() + 5);

		$file = $app->file('test.js');

		$this->assertSame($modifiedEnContent, $file->modified(null, null, 'en'));
		$this->assertSame($modifiedDeContent, $file->modified(null, null, 'de'));
	}

	public function testPanel()
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'test.pdf'
				]
			]
		]);

		$file = $page->file('test.pdf');
		$this->assertInstanceOf(Panel::class, $file->panel());
	}

	public function testPermalink()
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'test.pdf',
					'content'  => ['uuid' => 'my-file-uuid']
				]
			]
		]);

		$this->assertSame('//@/file/my-file-uuid', $page->file('test.pdf')->permalink());
	}

	public function testPreviewUrl()
	{
		$app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		$app->impersonate('test@getkirby.com');

		$page = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'test.pdf'
				]
			]
		]);

		$file = $page->file('test.pdf');
		$this->assertSame('/test/test.pdf', $file->previewUrl());
	}

	public function testPreviewUrlUnauthenticated()
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				[
					'filename' => 'test.pdf'
				]
			]
		]);

		$file = $page->file('test.pdf');
		$this->assertSame('/media/pages/test/' . $file->mediaHash() . '/test.pdf', $file->previewUrl());
	}

	public function testPreviewUrlForDraft()
	{
		$app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		$app->impersonate('test@getkirby.com');

		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true,
			'files'   => [
				[
					'filename' => 'test.pdf'
				]
			]
		]);

		$file = $page->file('test.pdf');
		$this->assertSame($file->url(), $file->previewUrl());
	}

	public function testPreviewUrlForPageWithDeniedPreviewSetting()
	{
		$app = new App([
			'blueprints' => [
				'pages/test' => [
					'options' => [
						'preview' => false
					]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
						'files'    => [
							[
								'filename' => 'test.pdf'
							]
						]
					]
				]
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		$app->impersonate('test@getkirby.com');

		$file = $app->file('test/test.pdf');
		$this->assertSame('/media/pages/test/' . $file->mediaHash() . '/test.pdf', $file->previewUrl());
	}

	public function testPreviewUrlForPageWithCustomPreviewSetting()
	{
		$app = new App([
			'blueprints' => [
				'pages/test' => [
					'options' => [
						'preview' => '/foo/bar'
					]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
						'files'    => [
							[
								'filename' => 'test.pdf'
							]
						]
					]
				]
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		$app->impersonate('test@getkirby.com');

		$file = $app->file('test/test.pdf');
		$this->assertSame($file->url(), $file->previewUrl());
	}

	public function testPreviewUrlForUserFile()
	{
		$app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		$app->impersonate('test@getkirby.com');

		$user = new User([
			'email' => 'test@getkirby.com',
			'files' => [
				[
					'filename' => 'test.pdf'
				]
			]
		]);

		$file = $user->file('test.pdf');
		$this->assertSame($file->url(), $file->previewUrl());
	}

	public function testPreviewUrlForExtendedComponent()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'components' => [
				'file::url' => fn ($kirby, $file, array $options = []) => 'https://getkirby.com/' . $file->filename()
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
						'files'    => [
							['filename' => 'test.pdf']
						]
					]
				]
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		$app->impersonate('test@getkirby.com');

		$file = $app->file('test/test.pdf');
		$this->assertSame('https://getkirby.com/test.pdf', $file->previewUrl());
	}

	public function testQuery()
	{
		$file = $this->file();

		$this->assertSame('cover.jpg', $file->query('file.filename'));
		$this->assertSame('cover.jpg', $file->query('model.filename'));
	}

	public function testApiUrl()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
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

		$this->assertSame('https://getkirby.com/api/site/files/site-file.jpg', $file->apiUrl());
		$this->assertSame('site/files/site-file.jpg', $file->apiUrl(true));

		// page file
		$file = $app->file('mother/child/page-file.jpg');

		$this->assertSame('https://getkirby.com/api/pages/mother+child/files/page-file.jpg', $file->apiUrl());
		$this->assertSame('pages/mother+child/files/page-file.jpg', $file->apiUrl(true));

		// user file
		$user = $app->user('test@getkirby.com');
		$file = $user->file('user-file.jpg');

		$this->assertSame('https://getkirby.com/api/users/test/files/user-file.jpg', $file->apiUrl());
		$this->assertSame('users/test/files/user-file.jpg', $file->apiUrl(true));
	}
}
