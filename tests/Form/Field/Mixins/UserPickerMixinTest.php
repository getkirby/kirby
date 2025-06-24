<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field;

class UserPickerMixinTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Fields.UserPickerMixin';

	public function setUp(): void
	{
		parent::setUp();

		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['email' => 'a@getkirby.com', 'role' => 'admin'],
				['email' => 'b@getkirby.com', 'role' => 'editor'],
				['email' => 'c@getkirby.com', 'role' => 'editor']
			]
		]);
	}

	public function testUsersWithoutQuery()
	{
		Field::$types = [
			'test' => [
				'mixins'  => ['userpicker'],
				'methods' => [
					'users' => fn () => $this->userpicker()['data']
				]
			]
		];

		$page = new Page([
			'slug' => 'test'
		]);

		$field = $this->field('test', [
			'model' => $page
		]);

		$users = $field->users();

		$this->assertCount(3, $users);
		$this->assertSame('a@getkirby.com', $users[0]['email']);
		$this->assertSame('b@getkirby.com', $users[1]['email']);
		$this->assertSame('c@getkirby.com', $users[2]['email']);
	}

	public function testUsersWithQuery()
	{
		Field::$types = [
			'test' => [
				'mixins'  => ['userpicker'],
				'methods' => [
					'users' => fn () => $this->userpicker([
						'query' => 'kirby.users.role("editor")'
					])['data']
				]
			]
		];

		$page = new Page([
			'slug' => 'test'
		]);

		$field = $this->field('test', [
			'model' => $page
		]);

		$users = $field->users();

		$this->assertCount(2, $users);
		$this->assertSame('b@getkirby.com', $users[0]['email']);
		$this->assertSame('c@getkirby.com', $users[1]['email']);
	}

	public function testMap()
	{
		Field::$types = [
			'test' => [
				'mixins'  => ['userpicker'],
				'methods' => [
					'users' => fn () => $this->userpicker([
						'map' => fn ($user) => $user->email()
					])['data']
				]
			]
		];

		$page = new Page([
			'slug' => 'test'
		]);

		$field = $this->field('test', [
			'model' => $page
		]);

		$users = $field->users();

		$this->assertSame([
			'a@getkirby.com',
			'b@getkirby.com',
			'c@getkirby.com',
		], $users);
	}
}
