<?php

namespace Kirby\Panel\Areas;

class SiteRequestsTest extends AreaTestCase
{
	public function testItemsUsersWithoutAccessToUsersArea(): void
	{
		$this->app([
			'roles' => [
				['name' => 'admin'],
				[
					'name'        => 'editor',
					'permissions' => [
						'access' => ['users' => false]
					]
				]
			],
			'users' => [
				[
					'id'    => 'admin',
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			],
			'request' => [
				'query' => [
					'_json' => true,
					'items' => 'admin'
				]
			]
		]);

		$this->login('editor@getkirby.com');

		$response = $this->response('items/users');
		$items    = json_decode($response->body(), true)['items'] ?? [];

		$this->assertSame(200, $response->code());
		$this->assertSame('admin', $items[0]['id']);
	}
}
