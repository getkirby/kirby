<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class RolesRoutesTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name'  => 'admin',
					'title' => 'Admin',
				],
				[
					'name'  => 'editor',
					'title' => 'Editor',
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testList()
	{
		$app = $this->app;

		$response = $app->api()->call('roles');

		$this->assertSame('admin', $response['data'][0]['name']);
		$this->assertSame('editor', $response['data'][1]['name']);
	}

	public function testGet()
	{
		$app = $this->app;

		$response = $app->api()->call('roles/editor');

		$this->assertSame('editor', $response['data']['name']);
		$this->assertSame('Editor', $response['data']['title']);
	}
}
