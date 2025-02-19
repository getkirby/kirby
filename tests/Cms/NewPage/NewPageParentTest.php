<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class NewPageParentTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageParentTest';

	public function testParent()
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

	public function testParentId()
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

	public function testParentPrevNext()
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

	public function testParentWithInvalidValue()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug'   => 'test',
			'parent' => 'some parent'
		]);
	}
}
