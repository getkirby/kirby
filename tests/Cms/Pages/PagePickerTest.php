<?php

namespace Kirby\Cms;

use Kirby\TestCase;
use Kirby\Exception\PermissionException;

class PagePickerTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'grandmother',
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
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testDefaults()
	{
		$picker = new PagePicker();

		$this->assertSame($this->app->site(), $picker->model());
		$this->assertCount(1, $picker->items());
		$this->assertSame('grandmother', $picker->items()->first()->id());
	}

	public function testParent()
	{
		$picker = new PagePicker([
			'parent' => 'grandmother'
		]);

		$this->assertCount(1, $picker->items());
		$this->assertSame('grandmother/mother', $picker->items()->first()->id());
		$this->assertSame('grandmother', $picker->model()->id());
	}

	public function testParentStart()
	{
		$picker = new PagePicker([
			'parent' => 'grandmother/mother'
		]);

		$this->assertSame($picker->start(), $this->app->site());
	}

	public function testParentAccessibleButNotListable(): void
	{
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

	public function testQueryAndParent()
	{
		$picker = new PagePicker([
			'query'  => 'site.find("grandmother").children',
			'parent' => 'grandmother/mother'
		]);

		$this->assertCount(3, $picker->items());
		$this->assertSame('grandmother/mother/child-a', $picker->items()->first()->id());
		$this->assertSame('grandmother/mother/child-c', $picker->items()->last()->id());
	}

	public function testQueryStart()
	{
		$picker = new PagePicker([
			'query'  => 'site.find("grandmother").children',
			'parent' => 'grandmother/mother'
		]);

		$this->assertSame('grandmother', $picker->start()->id());
	}
}
