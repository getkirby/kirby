<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Content\LockedContentException;
use Kirby\Content\PlainTextStorage;
use Kirby\Content\VersionId;
use Kirby\Data\Data;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageChangeTemplateTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageChangeTemplate';

	public function testChangeTemplateInMultiLanguageMode(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/video' => [
					'title'  => 'Video',
					'options' => [
						'template' => [
							'article'
						]
					],
					'fields' => [
						'caption' => [
							'type' => 'text'
						],
						'text' => [
							'type' => 'textarea'
						]
					]
				],
				'pages/article' => [
					'title'  => 'Article',
					'fields' => [
						'caption' => [
							'type' => 'radio'
						],
						'text' => [
							'type' => 'textarea'
						]
					]
				]
			],
			'hooks' => [
				'page.changeTemplate:before' => function (Page $page, $template) use ($phpunit, &$calls) {
					$phpunit->assertSame('video', $page->intendedTemplate()->name());
					$phpunit->assertSame('article', $template);
					$calls++;
				},
				'page.changeTemplate:after' => function (Page $newPage, Page $oldPage) use ($phpunit, &$calls) {
					$phpunit->assertSame('article', $newPage->intendedTemplate()->name());
					$phpunit->assertSame('video', $oldPage->intendedTemplate()->name());
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

		$page = Page::create([
			'slug'     => 'test',
			'template' => 'video',
		]);

		$page = $page->update([
			'title'   => 'Test',
			'caption' => 'Caption',
			'text'    => 'Text'
		], 'en');

		$page = $page->update([
			'title'   => 'Prüfen',
			'caption' => 'Untertitel',
			'text'    => 'Täxt'
		], 'de');

		$this->assertSame('video', $page->intendedTemplate()->name());
		$this->assertSame('Caption', $page->caption()->value());
		$this->assertSame('Text', $page->text()->value());
		$this->assertSame('Untertitel', $page->content('de')->get('caption')->value());
		$this->assertSame('Täxt', $page->content('de')->get('text')->value());

		$drafts            = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();
		$modified          = $page->changeTemplate('article');

		$this->assertSame('article', $modified->intendedTemplate()->name());
		$this->assertSame(2, $calls);

		$this->assertFileExists($modified->version('latest')->contentFile('en'));
		$this->assertFileExists($modified->version('latest')->contentFile('de'));
		$this->assertFileDoesNotExist($modified->version('latest')->contentFile('fr'));
		$this->assertNull($modified->caption()->value());
		$this->assertSame('Text', $modified->text()->value());
		$this->assertNull($modified->content('de')->get('caption')->value());
		$this->assertSame('Täxt', $modified->content('de')->get('text')->value());
	}

	public function testChangeTemplateInMultiLanguageModeWithPartialTranslation(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/video' => [
					'options' => [
						'template' => [
							'article'
						]
					],
					'fields' => [
						'text' => [
							'type' => 'textarea'
						]
					]
				],
				'pages/article' => [
					'fields' => [
						'text' => [
							'type' => 'textarea'
						]
					]
				]
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
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug'     => 'test',
			'template' => 'video',
		]);

		$page->version('latest')->save(['title' => 'Test', 'text' => 'Text'], 'en');
		$page->version('latest')->save(['title' => 'Prüfen'], 'de');

		$modified = $page->changeTemplate('article');

		// the translation must not inherit the text from the default language
		$this->assertSame('Text', $modified->version('latest')->read('en')['text']);
		$this->assertArrayNotHasKey('text', $modified->version('latest')->read('de'));
		$this->assertSame('Text', $modified->content('de')->text()->value(), 'should still fall back to the default language');
	}

	public function testChangeTemplateInSingleLanguageMode(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/video' => [
					'title'  => 'Video',
					'options' => [
						'template' => [
							'article'
						]
					],
					'fields' => [
						'caption' => [
							'type' => 'text'
						],
						'text' => [
							'type' => 'textarea'
						]
					]
				],
				'pages/article' => [
					'title'  => 'Article',
					'fields' => [
						'caption' => [
							'type' => 'info'
						],
						'text' => [
							'type' => 'textarea'
						]
					]
				]
			],
			'hooks' => [
				'page.changeTemplate:before' => function (Page $page, $template) use ($phpunit, &$calls) {
					$phpunit->assertSame('video', $page->intendedTemplate()->name());
					$phpunit->assertSame('article', $template);
					$calls++;
				},
				'page.changeTemplate:after' => function (Page $newPage, Page $oldPage) use ($phpunit, &$calls) {
					$phpunit->assertSame('article', $newPage->intendedTemplate()->name());
					$phpunit->assertSame('video', $oldPage->intendedTemplate()->name());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug'     => 'test',
			'template' => 'video',
			'content'  => [
				'title'   => 'Test',
				'caption' => 'Caption',
				'text'    => 'Text'
			]
		]);

		$drafts            = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();

		$this->assertSame('video', $page->intendedTemplate()->name());
		$this->assertSame('Caption', $page->caption()->value());
		$this->assertSame('Text', $page->text()->value());
		$this->assertFileExists($page->root() . '/video.txt');
		$this->assertFileDoesNotExist($page->root() . '/article.txt');

		$modified = $page->changeTemplate('article');

		$this->assertSame('article', $modified->intendedTemplate()->name());
		$this->assertNull($modified->caption()->value());
		$this->assertSame('Text', $modified->text()->value());
		$this->assertSame(2, $calls);
		$this->assertFileExists($modified->root() . '/article.txt');
		$this->assertFileDoesNotExist($modified->root() . '/video.txt');

		$this->assertSame($modified, $drafts->find('test'));
		$this->assertSame($modified, $childrenAndDrafts->find('test'));
	}

	public function testChangeTemplateOfTheErrorPage(): void
	{
		$page = Page::create([
			'slug' => 'error',
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the template for "error"');

		$page->changeTemplate('article');
	}

	public function testChangeTemplateToTheSameTemplate(): void
	{
		$page = Page::create([
			'slug'     => 'test',
			'template' => 'test',
		]);

		$modified = $page->changeTemplate('test');

		$this->assertSame($page, $modified);
	}

	public function testChangeTemplateWhenLockedByAnotherUser(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/video' => [
					'options' => [
						'template' => [
							'article'
						]
					]
				],
				'pages/article' => []
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug'     => 'test',
			'template' => 'video',
		]);

		$page->version('latest')->save(['title' => 'Title (latest)']);

		Data::write($page->version('changes')->contentFile(), [
			'title' => 'Title (changed)',
			'lock'  => 'editor'
		]);

		try {
			$page->changeTemplate('article');
			$this->fail('The locked page must not be converted');
		} catch (LockedContentException $e) {
			$this->assertSame('error.content.lock.update', $e->getCode());
		}

		// nothing must have been converted or deleted
		$this->assertFileExists($page->root() . '/video.txt');
		$this->assertFileExists($page->root() . '/_changes/video.txt');
		$this->assertFileDoesNotExist($page->root() . '/article.txt');
		$this->assertFileDoesNotExist($page->root() . '/_changes/article.txt');
	}

	public function testChangeTemplateWhenStorageFails(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/video' => [
					'options' => [
						'template' => [
							'article'
						]
					]
				],
				'pages/article' => []
			],
		]);

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug'     => 'test',
			'template' => 'video',
		]);

		$page->version('latest')->save(['title' => 'Title (latest)']);
		$page->version('changes')->save(['title' => 'Title (changed)']);

		$latest  = Data::read($page->root() . '/video.txt');
		$changes = Data::read($page->root() . '/_changes/video.txt');

		// storage that fails as soon as the converted content is written
		$page->changeStorage(new class ($page) extends PlainTextStorage {
			protected function write(VersionId $versionId, Language $language, array $fields): void
			{
				throw new Exception('The storage is not writable');
			}
		});

		try {
			$page->changeTemplate('article');
			$this->fail('The storage exception must be passed on');
		} catch (Exception $e) {
			$this->assertSame('The storage is not writable', $e->getMessage());
		}

		// the old versions must survive the failed conversion
		$this->assertSame($latest, Data::read($page->root() . '/video.txt'));
		$this->assertSame($changes, Data::read($page->root() . '/_changes/video.txt'));
		$this->assertFileDoesNotExist($page->root() . '/article.txt');
		$this->assertFileDoesNotExist($page->root() . '/_changes/article.txt');
	}

	public function testChangeTemplateWithChanges(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/video' => [
					'options' => [
						'template' => [
							'article'
						]
					]
				],
				'pages/article' => []
			],
		]);

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug'     => 'test',
			'template' => 'video',
		]);

		$page->version('latest')->save(['title' => 'Title (latest)']);
		$page->version('changes')->save(['title' => 'Title (changed)']);

		$modified = $page->changeTemplate('article');

		$this->assertSame('Title (latest)', $modified->version('latest')->content()->title()->value());
		$this->assertSame('Title (changed)', $modified->version('changes')->content()->title()->value());

		// the page must still be tracked as having unsaved changes
		$this->assertSame([$modified->uuid()->toString()], $this->app->cache('changes')->get('pages'));
	}
}
