<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UsersSearchController::class)]
#[CoversClass(ModelsSearchController::class)]
class UsersSearchControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Search.UsersSearchController';

	public function setUp(): void
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

	public function tearDown(): void
	{
		$this->tearDownTmp();
		App::destroy();
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

	public function testResult(): void
	{
		$controller = new UsersSearchController(query: 'simpson');
		$result     = $controller->result($controller->models()->first());

		$image = [
			'back' => 'black',
			'color' => 'gray-500',
			'cover' => false,
			'icon'  => 'user',
			'ratio' => '1/1'
		];

		$this->assertSame($image, $result['image']);
		$this->assertStringEndsWith('@simpson.com', $result['text']);
		$this->assertSame('Nobody', $result['info']);
		$this->assertArrayHasKey('link', $result);
		$this->assertArrayHasKey('uuid', $result);
	}
}
