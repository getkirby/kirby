<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use ReflectionClass;

class SiteActionsTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteActions';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'email' => 'admin@domain.com',
					'role'  => 'admin'
				]
			],
			'user' => 'admin@domain.com',
			'blueprints' => [
				'site' => [
					'name'   => 'site',
					'title'  => 'Site',
					'fields' => [
						'copyright' => [
							'type' => 'text'
						]
					]
				]
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function site()
	{
		return $this->app->site();
	}

	public function testChangeTitle()
	{
		$site = $this->site()->changeTitle('Test');
		$this->assertSame('Test', $site->title()->value());
	}

	public function testCreateChild()
	{
		$page = $this->site()->createChild([
			'slug'     => 'test',
			'template' => 'test',
		]);

		$this->assertSame('test', $page->slug());
		$this->assertSame('test', $page->intendedTemplate()->name());
	}

	public function testCreateFile()
	{
		F::write($source = static::TMP . '/source.md', '');

		$file = $this->site()->createFile([
			'filename' => 'test.md',
			'source'   => $source
		]);

		$this->assertSame('test.md', $file->filename());
	}

	public function testSave()
	{
		$site = $this->site()->clone(['content' => ['copyright' => 2012]])->save();
		$this->assertSame(2012, $site->copyright()->value());
	}

	public function testUpdate()
	{
		$site = $this->site()->update([
			'copyright' => '2018'
		]);

		$this->assertSame('2018', $site->copyright()->value());
	}

	public function testChangeTitleHooks()
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'site.changeTitle:before' => function (Site $site, $title, $languageCode) use ($phpunit, &$calls) {
					$phpunit->assertNull($site->title()->value());
					$phpunit->assertSame('New Title', $title);
					$phpunit->assertNull($languageCode);
					$calls++;
				},
				'site.changeTitle:after' => function (Site $newSite, Site $oldSite) use ($phpunit, &$calls) {
					$phpunit->assertSame('New Title', $newSite->title()->value());
					$phpunit->assertNull($oldSite->title()->value());
					$calls++;
				}
			]
		]);

		$app->site()->changeTitle('New Title');

		$this->assertSame(2, $calls);
	}

	public function testChangeTitleHookBeforeHookDefaultLanguage()
	{
		$calls = 0;
		$phpunit = $this;

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
			],
			'hooks' => [
				'site.changeTitle:before' => function (Site $site, $title, $languageCode) use ($phpunit, &$calls) {
					$phpunit->assertNull($site->title()->value());
					$phpunit->assertSame('New Title', $title);
					$phpunit->assertNull($languageCode);
					$calls++;
				}
			]
		]);

		$app->site()->changeTitle('New Title');

		$this->assertSame(1, $calls);
	}

	public function testChangeTitleHookBeforeHookSecondaryLanguage()
	{
		$calls = 0;
		$phpunit = $this;

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
			],
			'hooks' => [
				'site.changeTitle:before' => function (Site $site, $title, $languageCode) use ($phpunit, &$calls) {
					$phpunit->assertNull($site->title()->value());
					$phpunit->assertSame('New Title', $title);
					$phpunit->assertSame('de', $languageCode);
					$calls++;
				}
			]
		]);

		$app->site()->changeTitle('New Title', 'de');

		$this->assertSame(1, $calls);
	}

	public function testCommit(): void
	{
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'site.changeTitle:before' => [
					function (Site $site, string $title) use ($phpunit) {
						$phpunit->assertSame('target', $title);
						$phpunit->assertSame('original', $site->title()->value());
						// altering $site which will be passed
						// to subsequent hook
						return new Site(['content' => ['title' => 'a']]);
					},
					function (Site $site, string $title) use ($phpunit) {
						$phpunit->assertSame('target', $title);
						// altered $site from previous hook
						$phpunit->assertSame('a', $site->title()->value());
						// altering $site which will be used
						// in the commit callback closure
						return new Site(['content' => ['title' => 'b']]);
					}
				],
				'site.changeTitle:after' => [
					function (Site $newSite, Site $oldSite) use ($phpunit) {
						$phpunit->assertSame('original', $oldSite->title()->value());
						// modified $site from the commit callback closure
						$phpunit->assertSame('target', $newSite->title()->value());
						// altering $newSite which will be passed
						// to subsequent hook
						return new Site(['content' => ['title' => 'c']]);
					},
					function (Site $newSite, Site $oldSite) use ($phpunit) {
						$phpunit->assertSame('original', $oldSite->title()->value());
						// altered $newSite from previous hook
						$phpunit->assertSame('c', $newSite->title()->value());
						// altering $newSite which will be the final result
						return new Site(['content' => ['title' => 'd']]);
					}
				]
			]
		]);

		$app->impersonate('kirby');

		$site   = new Site(['content' => ['title' => 'original']]);
		$class  = new ReflectionClass($site);
		$commit = $class->getMethod('commit');
		$result = $commit->invokeArgs($site, [
			'changeTitle',
			['site' => $site, 'title' => 'target'],
			function (Site $site, string $title) use ($phpunit) {
				$phpunit->assertSame('target', $title);
				// altered $site from before hooks
				$phpunit->assertSame('b', $site->title()->value());
				return new Site(['content' => ['title' => $title]]);
			}
		]);

		// altered result from last after hook
		$this->assertSame('d', $result->title()->value());
	}

	public function testUpdateHooks()
	{
		$calls = 0;
		$phpunit = $this;
		$input = [
			'copyright' => 'Kirby'
		];

		$app = $this->app->clone([
			'hooks' => [
				'site.update:before' => function (Site $site, $values, $strings) use ($phpunit, $input, &$calls) {
					$phpunit->assertNull($site->copyright()->value());
					$phpunit->assertSame($input, $values);
					$phpunit->assertSame($input, $strings);
					$calls++;
				},
				'site.update:after' => function (Site $newSite, Site $oldSite) use ($phpunit, &$calls) {
					$phpunit->assertSame('Kirby', $newSite->copyright()->value());
					$phpunit->assertNull($oldSite->copyright()->value());
					$calls++;
				}
			]
		]);

		$app->site()->update($input);

		$this->assertSame(2, $calls);
	}

	public function testPurge()
	{
		// we're going to test it on translations because it's just that public propery
		$app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			],
			'site' => [
				'translations' => [
					[
						'code' => 'en',
						'content' => [
							'title' => 'Site',
						]
					],
					[
						'code' => 'de',
						'content' => [
							'title' => 'Seite',
						]
					],
				]
			]
		]);

		$site = $app->site();

		$site->children();
		$site->drafts();
		$site->childrenAndDrafts();

		$this->assertNotNull([], $site->translations);
		$this->assertNotNull($site->children);
		$this->assertNotNull($site->drafts);
		$this->assertNotNull($site->childrenAndDrafts);

		$site->purge();

		$this->assertNull($site->translations);
		$this->assertNull($site->children);
		$this->assertNull($site->drafts);
		$this->assertNull($site->childrenAndDrafts);
	}
}
