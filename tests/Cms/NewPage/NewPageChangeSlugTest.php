<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Page::class)]
class NewPageChangeSlugTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageChangeSlug';

	public static function slugProvider(): array
	{
		return [
			['test', 'test', true],
			['test', 'test', false],
			['modified-test', 'modified-test', true],
			['modified-test', 'modified-test', false],
			['mödified-tést', 'modified-test', true],
			['mödified-tést', 'modified-test', false]
		];
	}

	#[DataProvider('slugProvider')]
	public function testChangeSlugInMultiLanguageMode(
		string $input,
		string $expected,
		bool $draft
	): void {
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');
		$site = $this->app->site();

		// pre-populate caches
		$site->children();
		$site->drafts();
		$site->childrenAndDrafts();

		if ($draft) {
			$page = Page::create([
				'slug' => 'test',
			]);

			$in   = 'drafts';
			$root = static::TMP . '/content/_drafts/test';
		} else {
			$page = Page::create([
				'slug' => 'test',
				'num'  => 1
			]);

			$in   = 'children';
			$root = static::TMP . '/content/1_test';
		}

		$page = $page->update(['slug' => 'test-de'], 'de');

		$this->assertTrue($page->exists());
		$this->assertSame('test', $page->slug());
		$this->assertSame('test-de', $page->slug('de'));

		$this->assertTrue($page->parentModel()->$in()->has('test'));
		$this->assertSame($root, $page->root());

		$modified = $page->changeSlug($input, 'de');

		$this->assertTrue($modified->exists());
		$this->assertSame('test', $modified->slug());
		$this->assertSame($expected, $modified->slug('de'));
		$this->assertSame($modified, $site->$in()->get('test'));
		$this->assertSame($modified, $site->childrenAndDrafts()->get('test'));
		$this->assertSame($root, $modified->root());
	}

	#[DataProvider('slugProvider')]
	public function testChangeSlugInSingleLanguageMode(
		string $input,
		string $expected,
		bool $draft
	): void {
		$site = $this->app->site();

		// pre-populate caches
		$site->children();
		$site->drafts();
		$site->childrenAndDrafts();

		if ($draft) {
			$page = Page::create([
				'slug' => 'test',
			]);

			$in      = 'drafts';
			$oldRoot = static::TMP . '/content/_drafts/test';
			$newRoot = static::TMP . '/content/_drafts/' . $expected;
		} else {
			$page = Page::create([
				'slug' => 'test',
				'num'  => 1
			]);

			$in      = 'children';
			$oldRoot = static::TMP . '/content/1_test';
			$newRoot = static::TMP . '/content/1_' . $expected;
		}

		$this->assertTrue($page->exists());
		$this->assertSame('test', $page->slug());

		$this->assertTrue($page->parentModel()->$in()->has('test'));
		$this->assertSame($oldRoot, $page->root());

		$modified = $page->changeSlug($input);

		$this->assertTrue($modified->exists());
		$this->assertSame($expected, $modified->slug());
		$this->assertIsPage($modified, $site->$in()->get($expected));
		$this->assertIsPage($modified, $site->childrenAndDrafts()->get($expected));
		$this->assertSame($newRoot, $modified->root());
	}

	public function testChangeSlugHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'page.changeSlug:before' => function (Page $page, $slug, $languageCode) use ($phpunit, &$calls) {
					$phpunit->assertSame('test', $page->slug());
					$phpunit->assertSame('new-test', $slug);
					$phpunit->assertNull($languageCode);
					$calls++;
				},
				'page.changeSlug:after' => function (Page $newPage, Page $oldPage) use ($phpunit, &$calls) {
					$phpunit->assertSame('new-test', $newPage->slug());
					$phpunit->assertSame('test', $oldPage->slug());
					$calls++;
				}
			]
		]);

		$app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test'
		]);

		$page->changeSlug('new-test');

		$this->assertSame(2, $calls);
	}

}
