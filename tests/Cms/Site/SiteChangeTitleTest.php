<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteChangeTitleTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteChangeTitle';

	public function testChangeTitle(): void
	{
		$site = new Site();
		$site = $site->changeTitle('Test');
		$this->assertSame('Test', $site->title()->value());
	}

	public function testChangeTitleWhenChangesExist()
	{
		$site = new Site();

		// save the original title
		$site->version('latest')->save([
			'title' => 'Old Title'
		]);

		// add some changes
		$site->version('changes')->save([
			'text' => 'Some additional text'
		]);

		$modified = $site->changeTitle('New Title');

		$this->assertSame('New Title', $modified->title()->value());

		$changes = $modified->version('changes')->content();

		$this->assertSame('New Title', $changes->get('title')->value(), 'The title should be updated in the changes version');
		$this->assertSame('Some additional text', $changes->get('text')->value(), 'Other changes should remain the same');
	}

	public function testChangeTitleHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
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

		$this->app->impersonate('kirby');

		$site = new Site();
		$site->changeTitle('New Title');

		$this->assertSame(2, $calls);
	}

	public function testChangeTitleHookBeforeHookDefaultLanguage(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
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

		$this->app->impersonate('kirby');

		$site = new Site();
		$site->changeTitle('New Title');

		$this->assertSame(1, $calls);
	}

	public function testChangeTitleHookBeforeHookSecondaryLanguage(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
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

		$this->app->impersonate('kirby');

		$site = new Site();
		$site->changeTitle('New Title', 'de');

		$this->assertSame(1, $calls);
	}
}
