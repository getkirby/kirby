<?php

namespace Kirby\Cms;


use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteBreadcrumbTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteBreadcrumb';

	public function testBreadcrumb(): void
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'home',
				],
				[
					'slug' => 'grandma',
					'children' => [
						[
							'slug' => 'mother',
							'children' => [
								['slug' => 'child']
							]
						]
					]
				]
			]
		]);

		$site->visit('grandma/mother/child');

		$crumb = $site->breadcrumb();

		$this->assertSame($site->find('home'), $crumb->nth(0));
		$this->assertSame($site->find('grandma'), $crumb->nth(1));
		$this->assertSame($site->find('grandma/mother'), $crumb->nth(2));
		$this->assertSame($site->find('grandma/mother/child'), $crumb->nth(3));
	}

	public function testBreadcrumbSideEffects(): void
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'home',
				],
				[
					'slug' => 'grandma',
					'children' => [
						[
							'slug' => 'mother',
							'children' => [
								['slug' => 'child-a'],
								['slug' => 'child-b'],
								['slug' => 'child-c']
							]
						]
					]
				]
			]
		]);

		$page  = $site->visit('grandma/mother/child-b');
		$crumb = $site->breadcrumb();

		$this->assertSame($site->find('home'), $crumb->nth(0));
		$this->assertSame($site->find('grandma'), $crumb->nth(1));
		$this->assertSame($site->find('grandma/mother'), $crumb->nth(2));
		$this->assertSame($site->find('grandma/mother/child-b'), $crumb->nth(3));

		$this->assertSame('child-a', $page->prev()->slug());
		$this->assertSame('child-c', $page->next()->slug());
	}
}
