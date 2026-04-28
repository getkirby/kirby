<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\App;
use Kirby\Panel\Ui\Item\UserItem;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UsersSearchController::class)]
#[CoversClass(ModelsSearchController::class)]
class UsersSearchControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Search.UsersSearchController';

	protected function setUp(): void
	{
		$this->setUpTmp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				['email' => 'homer@simpson.com'],
				['email' => 'bart@simpson.com'],
				['email' => 'net@flanders.com']
			]
		]);

		$this->app->impersonate('kirby');
	}

	protected function tearDown(): void
	{
		$this->tearDownTmp();
		App::destroy();
	}

	public function testItem(): void
	{
		$controller = new UsersSearchController(query: 'simpson');
		$item       = $controller->item($controller->models()->first());
		$this->assertInstanceOf(UserItem::class, $item);
	}

	public function testLoad(): void
	{
		$controller = new UsersSearchController(query: 'simpson');
		$results    = $controller->load();
		$this->assertCount(2, $results['results']);
		$this->assertNull($results['pagination']);
	}

	public function testLoadPaginated(): void
	{
		$controller = new UsersSearchController(query:'simpson', limit: 1);
		$result     = $controller->load();
		$this->assertCount(1, $result['results']);
		$this->assertSame(1, $result['pagination']['page']);
		$this->assertSame(2, $result['pagination']['pages']);
		$this->assertSame(0, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(2, $result['pagination']['total']);

		$controller = new UsersSearchController(
			query:'simpson',
			limit: 1,
			page: 2
		);
		$result = $controller->load();
		$this->assertCount(1, $result['results']);
		$this->assertSame(2, $result['pagination']['page']);
		$this->assertSame(2, $result['pagination']['pages']);
		$this->assertSame(1, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(2, $result['pagination']['total']);
	}

	public function testModels(): void
	{
		$controller = new UsersSearchController(query:'simpson');
		$models     = $controller->models();

		$this->assertCount(2, $models);
		$this->assertEqualsCanonicalizing([
			'bart@simpson.com',
			'homer@simpson.com'
		], $models->values(fn ($model) => $model->email()));

		// without query
		$controller = new UsersSearchController();
		$models     = $controller->models();
		$this->assertCount(0, $models);
	}

	public function testModelsNotListable(): void
	{
		// use a uuid-based role to avoid static permission cache
		// collisions with roles used in other tests
		$uuid = uuid();

		$this->app = $this->app->clone([
			'blueprints' => [
				'users/restricted-' . $uuid => [
					'name'    => 'restricted-' . $uuid,
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'restricted-' . $uuid]
			],
			'users' => [
				[
					'email' => 'a@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'email' => 'b@getkirby.com',
					'role'  => 'restricted-' . $uuid
				],
				[
					'email' => 'c@getkirby.com',
					'role'  => 'restricted-' . $uuid
				]
			]
		]);

		// the kirby superuser bypasses all blueprint restrictions
		$this->app->impersonate('kirby');
		$controller = new UsersSearchController(query: 'getkirby.com');
		$models     = $controller->models();
		$this->assertCount(3, $models);
		$this->assertEqualsCanonicalizing([
			'a@getkirby.com',
			'b@getkirby.com',
			'c@getkirby.com'
		], $models->values(fn ($model) => $model->email()));

		// the editor can only access their own account
		$this->app->impersonate('a@getkirby.com');
		$controller = new UsersSearchController(query: 'getkirby.com');
		$models     = $controller->models();
		$this->assertCount(1, $models);
		$this->assertEqualsCanonicalizing([
			'a@getkirby.com'
		], $models->values(fn ($model) => $model->email()));
	}
}
