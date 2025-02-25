<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageMoveTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageMove';

	public function testMove(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'sections' => [
						'subpages' => [
							'type'     => 'pages',
							'template' => 'child'
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$parentA = Page::create([
			'slug'     => 'parent-a',
			'template' => 'parent'
		]);

		$parentB = Page::create([
			'slug'     => 'parent-b',
			'template' => 'parent'
		]);

		$child = Page::create([
			'parent'   => $parentB,
			'slug'     => 'child',
			'template' => 'child'
		]);

		$moved = $child->move($parentB);

		$this->assertTrue($moved->parent()->is($parentB));
	}
}
