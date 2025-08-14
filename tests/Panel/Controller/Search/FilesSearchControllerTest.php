<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\App;
use Kirby\Panel\Ui\Item\FileItem;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilesSearchController::class)]
#[CoversClass(ModelsSearchController::class)]
class FilesSearchControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Search.FilesSearchController';

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
								'slug' => 'beautiful-animals',
								'files' => [
									['filename' => 'green-fish.jpg'],
									['filename' => 'pink-fish.jpg']
								]
							]
						],
						'files' => [
							['filename' => 'blue-fish.jpg'],
							['filename' => 'red-fish.jpg']
						]
					]
				],
				'files' => [
					['filename' => 'purple-fish.jpg']
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
		$controller = new FilesSearchController(query:'fish');
		$item       = $controller->item($controller->models()->first());
		$this->assertInstanceOf(FileItem::class, $item);
	}

	public function testLoad(): void
	{
		$controller = new FilesSearchController(query:'fish');
		$results    = $controller->load();
		$this->assertCount(5, $results['results']);
		$this->assertNull($results['pagination']);
	}

	public function testLoadPaginated(): void
	{
		$controller = new FilesSearchController(query:'fish', limit: 1);
		$result     = $controller->load();
		$this->assertCount(1, $result['results']);
		$this->assertSame(1, $result['pagination']['page']);
		$this->assertSame(5, $result['pagination']['pages']);
		$this->assertSame(0, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(5, $result['pagination']['total']);

		$controller = new FilesSearchController(
			query:'fish',
			limit: 1,
			page: 2
		);
		$result = $controller->load();
		$this->assertCount(1, $result['results']);
		$this->assertSame(2, $result['pagination']['page']);
		$this->assertSame(5, $result['pagination']['pages']);
		$this->assertSame(1, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(5, $result['pagination']['total']);
	}

	public function testModels(): void
	{
		$controller = new FilesSearchController(query:'fish');
		$models     = $controller->models();

		$this->assertCount(5, $models);
		$this->assertEqualsCanonicalizing([
			'red-fish.jpg',
			'blue-fish.jpg',
			'pink-fish.jpg',
			'green-fish.jpg',
			'purple-fish.jpg'
		], $models->values(fn ($model) => $model->filename()));

		// without query
		$controller = new FilesSearchController();
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
						'slug' => 'photos',
						'files' => [
							['filename' => 'blue-fish.jpg'],
							[
								'filename' => 'red-fish.jpg',
								'template' => 'secret'
							]
						]
					]
				]
			],
			'blueprints' => [
				'files/secret' => [
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
		$controller = new FilesSearchController(query:'fish');
		$models     = $controller->models();
		$this->assertCount(1, $models);
		$this->assertEqualsCanonicalizing([
			'blue-fish.jpg'
		], $models->values(fn ($model) => $model->filename()));

		$this->app->impersonate('kirby');
		$controller = new FilesSearchController(query:'fish');
		$models     = $controller->models();
		$this->assertCount(2, $models);
		$this->assertEqualsCanonicalizing([
			'blue-fish.jpg',
			'red-fish.jpg'
		], $models->values(fn ($model) => $model->filename()));
	}
}
