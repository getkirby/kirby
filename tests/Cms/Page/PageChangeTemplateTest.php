<?php

namespace Kirby\Cms;

use Kirby\Content\VersionId;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageChangeTemplateTest extends NewModelTestCase
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

		$this->assertFileExists($modified->version(VersionId::latest())->contentFile('en'));
		$this->assertFileExists($modified->version(VersionId::latest())->contentFile('de'));
		$this->assertFileDoesNotExist($modified->version(VersionId::latest())->contentFile('fr'));
		$this->assertNull($modified->caption()->value());
		$this->assertSame('Text', $modified->text()->value());
		$this->assertNull($modified->content('de')->get('caption')->value());
		$this->assertSame('Täxt', $modified->content('de')->get('text')->value());
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
}
