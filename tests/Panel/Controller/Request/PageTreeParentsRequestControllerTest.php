<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageTreeParentsRequestController::class)]
class PageTreeParentsRequestControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Request.PageTreeParentsRequestController';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'articles',
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

	public function tearDown(): void
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

		$controller = new PageTreeParentsRequestController();
		$data       = $controller->load();
		$this->assertSame([
			'site://',
			'page://articles',
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

		$controller = new PageTreeParentsRequestController();
		$data       = $controller->load();
		$this->assertSame([
			'/',
			'articles',
			'articles/article'
		], $data['data']);
	}
}
