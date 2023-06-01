<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class FileTestModel extends File
{
}

/**
 * @coversDefaultClass \Kirby\Cms\File
 */
class FileTest extends TestCase
{
	protected function defaults(?App $kirby = null): array
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
		return new File(array_merge($this->defaults($props['kirby'] ?? null), $props));
	}

	public function testAsset()
	{
		$file = $this->file();
		$this->assertInstanceOf('Kirby\Filesystem\File', $file->asset());
		$this->assertSame('https://getkirby.com/projects/project-a/cover.jpg', $file->asset()->url());
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

	/**
	 * @covers ::contentFileData
	 */
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

		$this->assertSame($page, $file->page());

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
				'files/test' => [
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
			'template' => 'test'
		]);
		$this->assertFalse($file->isReadable());
		$this->assertFalse($file->isReadable()); // test caching
	}

	public function testMediaHash()
	{
		$app = new App([
			'roots' => [
				'index'   => $index = __DIR__ . '/fixtures/FileTest/mediaHash',
				'content' => $index
			],
			'options' => [
				'content.salt' => 'test'
			]
		]);

		F::write($index . '/test.jpg', 'test');
		touch($index . '/test.jpg', 5432112345);
		$file = $this->file([
			'kirby'    => $app,
			'parent'   => $app->site(),
			'filename' => 'test.jpg'
		]);

		$this->assertSame('08756f3115-5432112345', $file->mediaHash());

		Dir::remove(dirname($index));
	}

	public function testMediaToken()
	{
		$app = new App([
			'roots' => [
				'index'   => $index = __DIR__ . '/fixtures/FileTest/mediaHash',
				'content' => $index
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
				'index'   => $index = __DIR__ . '/fixtures/FileTest/modified',
				'content' => $index
			]
		]);

		// create a file
		F::write($file = $index . '/test.js', 'test');

		$modified = filemtime($file);
		$file     = $app->file('test.js');

		$this->assertSame($modified, $file->modified());

		// default date handler
		$format = 'd.m.Y';
		$this->assertSame(date($format, $modified), $file->modified($format));

		// custom date handler
		$format = '%d.%m.%Y';
		$this->assertSame(@strftime($format, $modified), $file->modified($format, 'strftime'));

		Dir::remove(dirname($index));
	}

	public function testModifiedContent()
	{
		$app = new App([
			'roots' => [
				'index'   => $index = __DIR__ . '/fixtures/FileTest/modified',
				'content' => $index
			]
		]);

		// create a file
		F::write($file = $index . '/test.js', 'test');
		touch($file, $modifiedFile = \time() + 2);

		F::write($content = $index . '/test.js.txt', 'test');
		touch($file, $modifiedContent = \time() + 5);

		$file = $app->file('test.js');

		$this->assertNotEquals($modifiedFile, $file->modified());
		$this->assertSame($modifiedContent, $file->modified());

		Dir::remove(dirname($index));
	}

	public function testModifiedSpecifyingLanguage()
	{
		$app = new App([
			'roots' => [
				'index'   => $index = __DIR__ . '/fixtures/FileTest/modified',
				'content' => $index
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
		F::write($index . '/test.js', 'test');

		// create the english content
		F::write($file = $index . '/test.js.en.txt', 'test');
		touch($file, $modifiedEnContent = \time() + 2);

		// create the german content
		F::write($file = $index . '/test.js.de.txt', 'test');
		touch($file, $modifiedDeContent = \time() + 5);

		$file = $app->file('test.js');

		$this->assertSame($modifiedEnContent, $file->modified(null, null, 'en'));
		$this->assertSame($modifiedDeContent, $file->modified(null, null, 'de'));

		Dir::remove(dirname($index));
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
		$this->assertInstanceOf('Kirby\Panel\File', $file->panel());
	}

	/**
	 * @covers ::permalink
	 */
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

	public function testPreviewUrlForDraft()
	{
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

	public function testPreviewUrlForPageWithCustomPreviewSetting()
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
			]
		]);

		$file = $app->file('test/test.pdf');
		$this->assertSame($file->url(), $file->previewUrl());
	}

	public function testPreviewUrlForUserFile()
	{
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
				'file::url' => function ($kirby, $file, array $options = []) {
					return 'https://getkirby.com/' . $file->filename();
				}
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
			]
		]);

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
