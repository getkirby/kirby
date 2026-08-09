<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
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

	public function testMoveRemovesTheOldMediaFolder(): void
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

		// simulate published media for the page and one of its subpages
		$grandchild = Page::create([
			'parent'   => $child,
			'slug'     => 'grandchild',
			'template' => 'child'
		]);

		F::write($media = $child->mediaRoot() . '/1234-5678/test.jpg', '');
		F::write($mediaOfGrandchild = $grandchild->mediaRoot() . '/1234-5678/test.jpg', '');

		$this->assertFileExists($media);
		$this->assertFileExists($mediaOfGrandchild);

		$moved = $child->move($parentB);

		$this->assertFileDoesNotExist($media);
		$this->assertFileDoesNotExist($mediaOfGrandchild);
		$this->assertDirectoryDoesNotExist($child->mediaRoot());
		$this->assertNotSame($child->mediaRoot(), $moved->mediaRoot());
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
