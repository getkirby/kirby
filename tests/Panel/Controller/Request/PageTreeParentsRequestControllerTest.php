<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageTreeParentsRequestController::class)]
class PageTreeParentsRequestControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Request.PageTreeParentsRequestController';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'articles',
						'template' => 'articles',
						'content'  => ['uuid' => 'articles'],
						'children' => [
							[
								'slug'     => 'article',
								'content'  => ['uuid' => 'article'],
								'children' => [
									['slug' => 'subarticle']
								]
							]
						]
					]
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
					'page' => 'articles/article/subarticle'
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageTreeParentsRequestController();
		$data       = $controller->load();
		$this->assertSame([
			'page://articles',
			'page://article'
		], $data['data']);


		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'page' => 'articles/article/subarticle',
					'root' => 'true'
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageTreeParentsRequestController();
		$data       = $controller->load();
		$this->assertSame([
			'site://',
			'page://articles',
			'page://article'
		], $data['data']);
	}

	public function testLoadNotListable(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			],
			'blueprints' => [
				'users/editor' => [
					'name'        => 'editor',
					'permissions' => [
						'pages' => ['*' => true]
					]
				],
				'pages/articles' => [
					'options' => ['list' => false]
				]
			],
			'request' => [
				'query' => [
					'page' => 'articles/article/subarticle'
				],
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$controller = new PageTreeParentsRequestController();
		$data       = $controller->load();
		$this->assertSame([
			'page://article'
		], $data['data']);
	}

	public function testLoadWhenUuidsDisabled(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				]
			],
			'request' => [
				'query' => [
					'page' => 'articles/article/subarticle'
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageTreeParentsRequestController();
		$data       = $controller->load();
		$this->assertSame([
			'articles',
			'articles/article'
		], $data['data']);


		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				]
			],
			'request' => [
				'query' => [
					'page' => 'articles/article/subarticle',
					'root' => 'true'
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageTreeParentsRequestController();
		$data       = $controller->load();
		$this->assertSame([
			'/',
			'articles',
			'articles/article'
		], $data['data']);
	}
}
