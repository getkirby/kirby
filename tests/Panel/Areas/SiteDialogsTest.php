<?php

namespace Kirby\Panel\Areas;

use Kirby\Cms\Page;

class SiteDialogsTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	public function testChangeTitle(): void
	{
		$dialog = $this->dialog('site/changeTitle');
		$props  = $dialog['props'];

		$this->assertFormDialog($dialog);

		$this->assertSame('Title', $props['fields']['title']['label']);
		$this->assertSame('Rename', $props['submitButton']);
		$this->assertNull($props['value']['title']);
	}

	public function testChangeTitleNotAccessible(): void
	{
		$this->app([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => ['access' => false]
					]
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor',
				]
			]
		]);

		$this->login('editor@getkirby.com');

		$dialog = $this->dialog('site/changeTitle');
		$this->assertSame('The site is not accessible', $dialog['error']);
	}

	public function testChangeTitleOnSubmit(): void
	{
		$this->submit([
			'title' => 'Test'
		]);

		$dialog = $this->dialog('site/changeTitle');

		$this->assertSame('site.changeTitle', $dialog['event']);
		$this->assertSame(200, $dialog['code']);

		$this->assertSame('Test', $this->app->site()->title()->value());
	}

	public function testChangeTitleOnSubmitNotAccessible(): void
	{
		$this->app([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => ['access' => false]
					]
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor',
				]
			],
			'request' => [
				'method' => 'POST',
				'body'   => ['title' => 'Test']
			]
		]);

		$this->login('editor@getkirby.com');

		$dialog = $this->dialog('site/changeTitle');
		$this->assertSame('The site is not accessible', $dialog['error']);
	}

	public function testChanges(): void
	{
		$dialog = $this->dialog('changes');
		$props  = $dialog['props'];

		$this->assertSame('k-changes-dialog', $dialog['component']);
		$this->assertSame([], $props['files']);
		$this->assertSame([], $props['pages']);
		$this->assertSame([], $props['users']);
	}

	public function testPageMove(): void
	{
		$this->app([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->login();

		$dialog = $this->dialog('pages/test/move');
		$props  = $dialog['props'];

		$this->assertSame('k-page-move-dialog', $dialog['component']);
		$this->assertSame('/pages/test', $props['value']['move']);
	}

	public function testPageMoveOnSubmitToSite(): void
	{
		$this->app([
			'blueprints' => [
				'site' => [
					'sections' => [
						'pages' => ['type' => 'pages']
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$parent = Page::create(['slug' => 'parent', 'template' => 'default']);
		Page::create(['parent' => $parent, 'slug' => 'child', 'template' => 'default']);

		$this->submit(['parent' => 'site://']);
		$this->login();

		$dialog = $this->dialog('pages/parent+child/move');

		$this->assertSame('page.move', $dialog['event']);
		$this->assertSame(200, $dialog['code']);
	}

	public function testPageMoveOnSubmitToSiteNotAccessible(): void
	{
		$this->app([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => ['access' => false]
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor',
				]
			],
			'request' => [
				'method' => 'POST',
				'body'   => ['parent' => 'site://']
			]
		]);

		$this->login('editor@getkirby.com');

		$dialog = $this->dialog('pages/test/move');
		$this->assertSame('The site is not accessible', $dialog['error']);
	}

	public function testPageMoveOnSubmitToSiteNotAccessibleWithSlash(): void
	{
		$this->app([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => ['access' => false]
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor',
				]
			],
			'request' => [
				'method' => 'POST',
				'body'   => ['parent' => '/']
			]
		]);

		$this->login('editor@getkirby.com');

		$dialog = $this->dialog('pages/test/move');
		$this->assertSame('The site is not accessible', $dialog['error']);
	}
}
