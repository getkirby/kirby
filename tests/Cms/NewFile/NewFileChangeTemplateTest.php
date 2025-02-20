<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use Kirby\Cms\NewPage as Page;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileChangeTemplateTest extends NewModelTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/files';
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileChangeTemplate';

	public function testChangeTemplate(): void
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

		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => [
				'template' => 'a',
				'caption'  => 'Caption',
				'text'     => 'Text'
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



		$this->assertSame('a', $file->template());
		$this->assertSame('This is the text', $file->text()->value());
		$this->assertSame('This is the caption', $file->caption()->value());

		$modified = $file->changeTemplate('b');

		$this->assertSame('b', $modified->template());
		$this->assertNull($modified->caption()->value());
		$this->assertSame('This is the text', $modified->text()->value());
		$this->assertSame(2, $calls);

		$modified->purge();
		$this->app->setCurrentLanguage('de');
		$this->assertNull($modified->caption()->value());
		$this->assertSame('Das ist der Text', $modified->text()->value());

		$this->assertFileExists($modified->version('latest')->contentFile('en'));
		$this->assertFileExists($modified->version('latest')->contentFile('de'));
		$this->assertFileDoesNotExist($modified->version('latest')->contentFile('fr'));
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

		$modified = $file->changeTemplate(null);
		$this->assertSame('default', $modified->template());
		$this->assertNull($modified->content()->get('template')->value());
	}

	public function testChangeTemplateInvalidAccept()
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

	public function testChangeTemplateManipulate()
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

	public function testChangeTemplateManipulateNonImage()
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
