<?php

namespace Kirby\Cms;


use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class PageParentTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageParent';

	public function testParent(): void
	{
		$mother = new Page([
			'slug' => 'mother',
		]);

		$child = new Page([
			'slug'   => 'child',
			'parent' => $mother
		]);

		$this->assertNull($mother->parent());
		$this->assertSame($mother, $child->parent());
	}

	public function testParentId(): void
	{
		$mother = new Page([
			'slug' => 'mother',
		]);

		$child = new Page([
			'slug'   => 'child',
			'parent' => $mother
		]);

		$this->assertNull($mother->parentId());
		$this->assertSame('mother', $child->parentId());
	}

	public function testParentPrevNext(): void
	{
		$root = new Page([
			'slug' => 'root',
			'children' => [
				[
					'slug' => 'projects',
					'children' => [
						[
							'slug' => 'project-a',
						],
						[
							'slug' => 'project-b',
						]
					]
				],
				[
					'slug' => 'blog'
				]
			]
		]);

		$child = $root->find('projects/project-a');
		$blog  = $root->find('blog');

		$this->assertSame($blog, $child->parent()->next());
		$this->assertNull($child->parent()->prev());
	}

	public function testParentWithInvalidValue(): void
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug'   => 'test',
			'parent' => 'some parent'
		]);
	}
}
