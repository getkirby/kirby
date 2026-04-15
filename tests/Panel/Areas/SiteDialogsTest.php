<?php

namespace Kirby\Panel\Areas;

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
}
