<?php

namespace Kirby\Cms;


use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageStatesTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageStates';

	public function family(): Site
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'grandma',
						'children' => [
							[
								'slug'     => 'mother',
								'children' => [
									[
										'slug' => 'child'
									]
								]
							]
						]
					]
				]
			]
		]);

		return $this->app->site();
	}

	public function testIsActive(): void
	{
		$family  = $this->family();
		$mother = $family->find('grandma/mother');
		$child  = $family->find('grandma/mother/child');

		$this->assertFalse($mother->isActive());
		$this->assertFalse($child->isActive());

		$family->visit('grandma/mother');

		$this->assertTrue($mother->isActive());
		$this->assertFalse($child->isActive());

		$family->visit('grandma/mother/child');

		$this->assertFalse($mother->isActive());
		$this->assertTrue($child->isActive());
	}

	public function testIsAncestorOf(): void
	{
		$family  = $this->family();
		$grandma = $family->find('grandma');
		$mother  = $grandma->find('mother');
		$child   = $mother->find('child');

		$this->assertTrue($mother->isAncestorOf($child));
		$this->assertTrue($grandma->isAncestorOf($child));
	}

	public function testIsChildOf(): void
	{
		$family  = $this->family();
		$grandma = $family->find('grandma');
		$mother  = $grandma->find('mother');
		$child   = $mother->find('child');

		$this->assertTrue($mother->isChildOf($grandma));
		$this->assertTrue($child->isChildOf($mother));
		$this->assertTrue($child->isChildOf($mother->id()));
		$this->assertFalse($grandma->isChildOf($mother));
		$this->assertFalse($child->isChildOf($grandma));
		$this->assertFalse($child->isChildOf('gibberish'));
		$this->assertFalse($child->isChildOf(null));
	}

	public function testIsDescendantOf(): void
	{
		$family  = $this->family();
		$grandma = $family->find('grandma');
		$mother  = $grandma->find('mother');
		$child   = $mother->find('child');

		$this->assertTrue($child->isDescendantOf($mother));
		$this->assertTrue($child->isDescendantOf('grandma/mother'));
		$this->assertTrue($child->isDescendantOf($grandma));
		$this->assertTrue($child->isDescendantOf('grandma'));
		$this->assertFalse($child->isDescendantOf('does/not/exist'));
	}

	public function testIsDescendantOfActive(): void
	{
		$family  = $this->family();
		$grandma = $family->find('grandma');
		$mother  = $grandma->find('mother');
		$child   = $mother->find('child');

		$family->visit('grandma');

		$this->assertFalse($grandma->isDescendantOfActive());
		$this->assertTrue($mother->isDescendantOfActive());
		$this->assertTrue($child->isDescendantOfActive());
	}

	public function testIsOpen(): void
	{
		$family  = $this->family();
		$mother = $family->find('grandma/mother');
		$child  = $family->find('grandma/mother/child');

		$this->assertFalse($mother->isOpen());
		$this->assertFalse($child->isOpen());

		$family->visit('grandma/mother');

		$this->assertTrue($mother->isOpen());
		$this->assertFalse($child->isOpen());

		$family->visit('grandma/mother/child');

		$this->assertTrue($mother->isOpen());
		$this->assertTrue($child->isOpen());
	}

}
