<?php

namespace Kirby\Cms;

use Kirby\Content\PlainTextStorage;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileChangeTemplateTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileChangeTemplate';

	public function testChangeTemplate(): void
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'roots' => [
				'index' => static::TMP
			],
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
						'template' => 'test'
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

		$page = $app->page('test');
		$file = $page->createFile([
			'filename' => 'test.jpg',
			'source'   => self::FIXTURES . '/test.jpg',
			'template' => 'a',
			'content'  => [
				'caption' => 'Caption',
				'text'    => 'Text'
			]
		]);

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
	}

	public function testChangeTemplateMultilang(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
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

		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$file = new File([
			'filename'     => 'test.jpg',
			'parent'       => $page,
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
		]);

		// make all tests below with real content files
		$file->changeStorage(PlainTextStorage::class);

		$this->assertSame('a', $file->template());

		$contentEN = $file->content('en');
		$contentDE = $file->content('de');
		$contentFR = $file->content('fr');

		$this->assertSame('This is the text', $contentEN->text()->value());
		$this->assertSame('This is the caption', $contentEN->caption()->value());

		$this->assertSame('Das ist der Text', $contentDE->text()->value());
		$this->assertSame('Das ist die Caption', $contentDE->caption()->value());

		$this->assertSame('This is the text', $contentFR->text()->value(), 'should fall back to the default language');
		$this->assertSame('This is the caption', $contentFR->caption()->value(), 'should fall back to the default language');

		// check for content files
		$this->assertFileExists($file->version('latest')->contentFile('en'));
		$this->assertFileExists($file->version('latest')->contentFile('de'));
		$this->assertFileDoesNotExist($file->version('latest')->contentFile('fr'), 'French does not have any content and should not have a meta file');

		$modified = $file->changeTemplate('b');

		$this->assertSame('b', $modified->template());
		$this->assertSame(2, $calls);

		$this->assertInstanceOf(PlainTextStorage::class, $modified->storage());

		// check which meta files have been created
		$this->assertFileExists($modified->version('latest')->contentFile('en'));
		$this->assertFileExists($modified->version('latest')->contentFile('de'));
		$this->assertFileDoesNotExist($modified->version('latest')->contentFile('fr'), 'French does not have any content and should not have a meta file');

		$contentEN = $modified->content('en');
		$contentDE = $modified->content('de');
		$contentFR = $modified->content('fr');

		$this->assertSame('This is the text', $contentEN->text()->value());
		$this->assertNull($contentEN->caption()->value(), 'The caption should be null because it turned into an info field');

		$this->assertSame('Das ist der Text', $contentDE->text()->value());
		$this->assertNull($contentDE->caption()->value(), 'The caption should be null because it turned into an info field');

		$this->assertSame('This is the text', $contentFR->text()->value(), 'should fall back to the default language');
		$this->assertNull($contentFR->caption()->value(), 'should fall back to the default language');
	}

	public function testChangeTemplateDefault(): void
	{
		$this->app = $this->app->clone([
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
			]
		]);

		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test-default',
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => ['template' => 'for-default-a']
		]);

		$this->assertSame('for-default-a', $file->template());
		$this->assertSame('for-default-a', $file->content()->get('template')->value());

		$modified = $file->changeTemplate('default');
		$this->assertSame('default', $modified->template());
		$this->assertNull($modified->content()->get('template')->value());

		$back = $modified->changeTemplate('for-default-b');
		$this->assertSame('for-default-b', $back->template());
		$this->assertSame('for-default-b', $back->content()->get('template')->value());

		$modified = $back->changeTemplate(null);
		$this->assertSame('default', $modified->template());
		$this->assertNull($modified->content()->get('template')->value());
	}

	public function testChangeTemplateInvalidAccept(): void
	{
		$this->app = $this->app->clone([
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
			]
		]);

		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test-default',
		]);

		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page,
			'content'  => ['template' => 'for-default-a']
		]);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The template for the file "test/test.pdf" cannot be changed to "for-default-b" (valid: "for-default-c, for-default-d")');

		$file->changeTemplate('for-default-b');
	}

	public function testChangeTemplateManipulate(): void
	{
		$testImage = static::FIXTURES . '/test.jpg';

		$this->app = $this->app->clone([
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
			]
		]);

		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test-default',
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => ['template' => 'manipulate-a']
		]);

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

	public function testChangeTemplateManipulateNonImage(): void
	{
		$this->app = $this->app->clone([
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

		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test-default',
		]);

		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page,
			'content'  => ['template' => 'manipulate-a']
		]);

		$newFile = $file->changeTemplate('manipulate-b');

		$this->assertSame('pdf', $file->extension());
		$this->assertSame('pdf', $newFile->extension());
	}
}
