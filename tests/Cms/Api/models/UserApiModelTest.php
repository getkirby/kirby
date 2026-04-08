<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class UserApiModelTest extends ApiModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserApiModel';

	protected $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = new User(['email' => 'test@getkirby.com']);
	}

	public function testFiles()
	{
		$this->app->impersonate('kirby');

		$user = new User([
			'email' => 'test@getkirby.com',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
			]
		]);

		$model = $this->api->resolve($user)->select('files')->toArray();

		$this->assertSame('a.jpg', $model['files'][0]['filename']);
		$this->assertSame('b.jpg', $model['files'][1]['filename']);
	}

	public function testNextSkipsInaccessibleUser(): void
	{
		$app = new App([
			'blueprints' => [
				'users/restricted' => [
					'options' => ['access' => false]
				]
			],
			'roles' => [
				['name' => 'editor'],
				['name' => 'restricted'],
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['email' => 'a@test.com', 'name' => 'A User', 'role' => 'editor'],
				['email' => 'b@test.com', 'name' => 'B User', 'role' => 'editor'],
				['email' => 'c@test.com', 'name' => 'C User', 'role' => 'restricted'],
				['email' => 'd@test.com', 'name' => 'D User', 'role' => 'editor'],
			]
		]);

		$app->impersonate('b@test.com');
		$user   = $app->user('b@test.com');
		$result = $app->api()->resolve($user)->select('next')->toArray();

		$this->assertSame('D User', $result['next']['name']);
	}

	public function testPrevSkipsInaccessibleUser(): void
	{
		$app = new App([
			'blueprints' => [
				'users/restricted' => [
					'options' => ['access' => false]
				]
			],
			'roles' => [
				['name' => 'editor'],
				['name' => 'restricted'],
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['email' => 'a@test.com', 'name' => 'A User', 'role' => 'editor'],
				['email' => 'b@test.com', 'name' => 'B User', 'role' => 'restricted'],
				['email' => 'c@test.com', 'name' => 'C User', 'role' => 'editor'],
				['email' => 'd@test.com', 'name' => 'D User', 'role' => 'editor'],
			]
		]);

		$app->impersonate('c@test.com');
		$user   = $app->user('c@test.com');
		$result = $app->api()->resolve($user)->select('prev')->toArray();

		$this->assertSame('A User', $result['prev']['name']);
	}

	public function testImage()
	{
		$image = $this->attr($this->user, 'panelImage');
		$expected = [
			'back' => 'black',
			'color' => 'gray-500',
			'cover' => false,
			'icon'  => 'user',
			'ratio' => '1/1'
		];

		$this->assertSame($expected, $image);
	}
}
