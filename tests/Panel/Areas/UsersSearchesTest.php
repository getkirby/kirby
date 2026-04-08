<?php

namespace Kirby\Panel\Areas;

class UsersSearchesTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->app([
			'roles' => [
				[
					'id'    => 'admin',
					'name'  => 'admin',
					'title' => '<strong>Admin'
				]
			]
		]);
		$this->install();
		$this->login();
	}

	public function testUserSearchWithNonListableUsers(): void
	{
		// use uuid-based roles and user IDs to avoid static permission cache collisions
		$uuid = uuid();

		$this->app([
			'blueprints' => [
				'users/manager-' . $uuid => [
					'name' => 'manager-' . $uuid,
				],
				'users/restricted-' . $uuid => [
					'name'    => 'restricted-' . $uuid,
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'manager-' . $uuid],
				['name' => 'restricted-' . $uuid]
			],
			'users' => [
				[
					'id'    => 'manager-' . $uuid,
					'email' => 'manager@getkirby.com',
					'role'  => 'manager-' . $uuid
				],
				[
					'id'    => 'restricted-' . $uuid,
					'email' => 'restricted@getkirby.com',
					'role'  => 'restricted-' . $uuid
				]
			],
			'request' => [
				'query' => [
					'query' => 'getkirby.com'
				]
			]
		]);

		$this->login('manager@getkirby.com');

		$results = $this->search('users')['results'];
		$this->assertCount(1, $results);
		$this->assertSame('manager@getkirby.com', $results[0]['text']);
	}

	public function testUserSearch(): void
	{
		$this->app([
			'request' => [
				'query' => [
					'query' => 'test'
				]
			]
		]);

		$this->login();

		$results = $this->search('users')['results'];

		$this->assertCount(1, $results);

		$image = [
			'back' => 'black',
			'color' => 'gray-500',
			'cover' => false,
			'icon'  => 'user',
			'ratio' => '1/1'
		];

		$this->assertSame($image, $results[0]['image']);
		$this->assertSame('test@getkirby.com', $results[0]['text']);
		$this->assertSame('/account', $results[0]['link']);
		$this->assertSame('&lt;strong&gt;Admin', $results[0]['info']);
	}
}
