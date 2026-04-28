<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserItemsRequestController::class)]
#[CoversClass(ModelItemsRequestController::class)]
class UserItemsRequestControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Request.UserItemsRequestController';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'id'    => 'homer',
					'email' => 'homer@getkirby.com'
				],
				[
					'id'    => 'bart',
					'email' => 'bart@getkirby.com'
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	protected function tearDown(): void
	{
		App::destroy();
	}

	public function testLoad(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'items' => 'user://homer,user://foo,user://bart'
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new UserItemsRequestController();
		$data       = $controller->load();
		$this->assertSame('homer@getkirby.com', $data['items'][0]['text']);
		$this->assertNull($data['items'][1]);
		$this->assertSame('bart@getkirby.com', $data['items'][2]['text']);
	}

	public function testLoadNotListable(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'users/restricted' => [
					'name'    => 'restricted',
					'options' => ['list' => false]
				]
			],
			'users' => [
				[
					'id'    => 'admin',
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'id'    => 'homer',
					'email' => 'homer@getkirby.com',
					'role'  => 'restricted'
				],
				[
					'id'    => 'bart',
					'email' => 'bart@getkirby.com'
				]
			],
			'request' => [
				'query' => [
					'items' => 'user://homer,user://bart'
				],
			]
		]);
		$this->app->impersonate('admin@getkirby.com');

		$controller = new UserItemsRequestController();
		$data       = $controller->load();
		$this->assertNull($data['items'][0]);
		$this->assertSame('bart@getkirby.com', $data['items'][1]['text']);
	}
}
