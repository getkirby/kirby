<?php

namespace Kirby\Cms;


use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(Page::class)]
class PageCommitTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageCommit';

	public function testCommit(): void
	{
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'page.changeSlug:before' => [
					function (Page $page, string $slug) use ($phpunit) {
						$phpunit->assertSame('target', $slug);
						$phpunit->assertSame('original', $page->slug());
						// altering $page which will be passed
						// to subsequent hook
						return new Page(['slug' => 'a']);
					},
					function (Page $page, string $slug) use ($phpunit) {
						$phpunit->assertSame('target', $slug);
						// altered $page from previous hook
						$phpunit->assertSame('a', $page->slug());
						// altering $page which will be used
						// in the commit callback closure
						return new Page(['slug' => 'b']);
					}
				],
				'page.changeSlug:after' => [
					function (Page $newPage, Page $oldPage) use ($phpunit) {
						$phpunit->assertSame('original', $oldPage->slug());
						// modified $page from the commit callback closure
						$phpunit->assertSame('target', $newPage->slug());
						// altering $newPage which will be passed
						// to subsequent hook
						return new Page(['slug' => 'c']);
					},
					function (Page $newPage, Page $oldPage) use ($phpunit) {
						$phpunit->assertSame('original', $oldPage->slug());
						// altered $newPage from previous hook
						$phpunit->assertSame('c', $newPage->slug());
						// altering $newPage which will be the final result
						return new Page(['slug' => 'd']);
					}
				]
			]
		]);

		$app->impersonate('kirby');

		$page   = new Page(['slug' => 'original']);
		$class  = new ReflectionClass($page);
		$commit = $class->getMethod('commit');
		$result = $commit->invokeArgs($page, [
			'changeSlug',
			['page' => $page, 'slug' => 'target'],
			function (Page $page, string $slug) use ($phpunit) {
				$phpunit->assertSame('target', $slug);
				// altered $page from before hooks
				$phpunit->assertSame('b', $page->slug());
				return new Page(['slug' => $slug]);
			}
		]);

		// altered result from last after hook
		$this->assertSame('d', $result->slug());
	}
}
