<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageMoveTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PageMove';

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
			'parent'   => $parentA,
			'slug'     => 'child',
			'template' => 'child'
		]);

		$moved = $child->move($parentB);

		$this->assertTrue($moved->parent()->is($parentB));
	}

	public function testMoveWhenTheParentIsTheSame(): void
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

		$parent = Page::create([
			'slug'     => 'parent',
			'template' => 'parent'
		]);

		$child = Page::create([
			'parent'   => $parent,
			'slug'     => 'child',
			'template' => 'child'
		]);

		$moved = $child->move($parent);

		$this->assertSame($child, $moved);
		$this->assertSame($child->parent(), $moved->parent());
	}
}
