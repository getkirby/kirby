<?php

namespace Kirby\Cms;

use Kirby\Cms\NewSite as Site;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(Site::class)]
class NewSiteCommitTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewSiteCommit';

	public function testCommit(): void
	{
		$phpunit = $this;

		$this->app = $this->app->clone([
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

		$this->app->impersonate('kirby');

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
}
