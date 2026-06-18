<?php

namespace Kirby\Cms;

use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PagePickerTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PagePicker';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'grandmother',
						'children' => [
							[
								'slug' => 'mother',
								'children' => [
									['slug' => 'child-a', 'template' => 'child-a'],
									['slug' => 'child-b', 'template' => 'child-b'],
									['slug' => 'child-c', 'template' => 'child-c']
								]
							]
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testDefaults(): void
	{
		$picker = new PagePicker();

		$this->assertSame($this->app->site(), $picker->model());
		$this->assertCount(1, $picker->items());
		$this->assertSame('grandmother', $picker->items()->first()->id());
	}

	public function testParent(): void
	{
		$picker = new PagePicker([
			'parent' => 'grandmother'
		]);

		$this->assertCount(1, $picker->items());
		$this->assertSame('grandmother/mother', $picker->items()->first()->id());
		$this->assertSame('grandmother', $picker->model()->id());
	}

	public function testParentStart(): void
	{
		$picker = new PagePicker([
			'parent' => 'grandmother/mother'
		]);

		$this->assertSame($picker->start(), $this->app->site());
	}

	public function testParentAccessibleButNotListable(): void
	{
		ModelPermissions::$cache = [];

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/limited' => [
					'options' => [
						'list' => false
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'visible-page',
						'template' => 'limited'
					]
				]
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');

		$page = $this->app->page('visible-page');
		$this->assertTrue($page->isAccessible());
		$this->assertFalse($page->isListable());

		// accessible-but-not-listable pages are valid picker parents
		$picker = new PagePicker(['parent' => 'visible-page']);

		$this->assertSame($page, $picker->parent());
	}

	public function testParentNotAccessibleFallsBackToSite(): void
	{
		ModelPermissions::$cache = [];

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/forbidden' => [
					'options' => [
						'access' => false
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'hidden-page',
						'template' => 'forbidden'
					]
				]
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');

		$page = $this->app->page('hidden-page');
		$this->assertFalse($page->isAccessible());
		$this->assertTrue($this->app->site()->isAccessible());

		// inaccessible parents must fall back to the site
		$picker = new PagePicker(['parent' => 'hidden-page']);

		$this->assertSame($this->app->site(), $picker->parent());
	}

	public function testParentAndSiteNotAccessibleThrows(): void
	{
		ModelPermissions::$cache = [];

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/forbidden' => [
					'options' => [
						'access' => false
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'hidden-page',
						'template' => 'forbidden'
					]
				]
			],
			'roles' => [
				['name' => 'admin'],
				[
					'name' => 'editor',
					'permissions' => [
						'site' => [
							'access' => false
						]
					]
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');

		$this->assertFalse($this->app->page('hidden-page')->isAccessible());
		$this->assertFalse($this->app->site()->isAccessible());

		$picker = new PagePicker(['parent' => 'hidden-page']);

		$this->expectException(PermissionException::class);
		$picker->parent();
	}

	public function testQuery(): void
	{
		$picker = new PagePicker([
			'query' => 'site.find("grandmother/mother").children'
		]);

		$this->assertCount(3, $picker->items());
		$this->assertSame('grandmother/mother/child-a', $picker->items()->first()->id());
		$this->assertSame('grandmother/mother/child-c', $picker->items()->last()->id());
	}

	public function testQueryAndParent(): void
	{
		$picker = new PagePicker([
			'query'  => 'site.find("grandmother").children',
			'parent' => 'grandmother/mother'
		]);

		$this->assertCount(3, $picker->items());
		$this->assertSame('grandmother/mother/child-a', $picker->items()->first()->id());
		$this->assertSame('grandmother/mother/child-c', $picker->items()->last()->id());
	}

	public function testQueryStart(): void
	{
		$picker = new PagePicker([
			'query'  => 'site.find("grandmother").children',
			'parent' => 'grandmother/mother'
		]);

		$this->assertSame('grandmother', $picker->start()->id());
	}

	public function testSameQueryAndParent(): void
	{
		$picker = new PagePicker([
			'query'  => 'site.find("grandmother/mother").children.filterBy("intendedTemplate", "in", ["child-a", "child-c"])',
			'parent' => 'grandmother/mother'
		]);

		$this->assertCount(2, $picker->items());
		$this->assertSame('grandmother/mother/child-a', $picker->items()->first()->id());
		$this->assertSame('grandmother/mother/child-c', $picker->items()->last()->id());
	}
}
