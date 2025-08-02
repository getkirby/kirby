<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\App;
use Kirby\Panel\Ui\Item\PageItem;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PagesSearchController::class)]
#[CoversClass(ModelsSearchController::class)]
class PagesSearchControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Search.PagesSearchController';

	public function setUp(): void
	{
		$this->setUpTmp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'articles',
						'children' => [
							['slug' => 'beautiful-flowers'],
							['slug' => 'beautiful-trees'],
							[
								'slug'    => 'beautiful-animals',
								'content' => [
									'title' => 'Beautiful animals'
								]
							]
						],
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
		App::destroy();
	}
	public function testItem(): void
	{
		$controller = new PagesSearchController(query: 'beautiful');
		$item       = $controller->item($controller->models()->first());
		$this->assertInstanceOf(PageItem::class, $item);
	}

	public function testLoad(): void
	{
		$controller = new PagesSearchController(query: 'beautiful');
		$results    = $controller->load();
		$this->assertCount(3, $results['results']);
		$this->assertNull($results['pagination']);
	}

	public function testLoadPaginated(): void
	{
		$controller = new PagesSearchController(query:'beautiful', limit: 1);
		$result     = $controller->load();
		$this->assertCount(1, $result['results']);
		$this->assertSame(1, $result['pagination']['page']);
		$this->assertSame(3, $result['pagination']['pages']);
		$this->assertSame(0, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(3, $result['pagination']['total']);

		$controller = new PagesSearchController(
			query:'beautiful',
			limit: 1,
			page: 2
		);
		$result = $controller->load();
		$this->assertCount(1, $result['results']);
		$this->assertSame(2, $result['pagination']['page']);
		$this->assertSame(3, $result['pagination']['pages']);
		$this->assertSame(1, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(3, $result['pagination']['total']);
	}

	public function testModels(): void
	{
		$controller = new PagesSearchController(query:'beautiful');
		$models     = $controller->models();

		$this->assertCount(3, $models);
		$this->assertEqualsCanonicalizing([
			'beautiful-animals',
			'beautiful-flowers',
			'beautiful-trees'
		], $models->values(fn ($model) => $model->slug()));

		// without query
		$controller = new PagesSearchController();
		$models     = $controller->models();
		$this->assertCount(0, $models);
	}

	public function testModelsNotListable(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a-friend',
						'content' => [
							'title' => 'A friend'
						]
					],
					[
						'slug'     => 'a-secret-friend',
						'template' => 'secret',
						'content'  => [
							'title' => 'A secret friend'
						]
					]
				]
			],
			'blueprints' => [
				'pages/secret' => [
					'options' => [
						'list' => [
							'*'      => true,
							'editor' => false
						]
					]
				]
			],
			'users' => [
				[
					'email' => 'homer@simpson.com',
					'role'  => 'editor'
				],
			],
			'roles' => [
				['name'  => 'editor']
			]
		]);

		$this->app->impersonate('homer@simpson.com');
		$controller = new PagesSearchController(query:'friend');
		$models     = $controller->models();
		$this->assertCount(1, $models);
		$this->assertEqualsCanonicalizing([
			'A friend'
		], $models->values(fn ($model) => $model->title()->value()));

		$this->app->impersonate('kirby');
		$controller = new PagesSearchController(query:'friend');
		$models     = $controller->models();
		$this->assertCount(2, $models);
		$this->assertEqualsCanonicalizing([
			'A friend',
			'A secret friend'
		], $models->values(fn ($model) => $model->title()->value()));
	}
}
