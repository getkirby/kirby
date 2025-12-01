<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageItemsRequestController::class)]
#[CoversClass(ModelItemsRequestController::class)]
class PageItemsRequestControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Request.PageItemsRequestController';

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
					'items' => 'page://articles,page://foo,page://article'
				],
			]
		]);

		$controller = new PageItemsRequestController();
		$data       = $controller->load();
		$this->assertSame('articles', $data['items'][0]['id']);
		$this->assertNull($data['items'][1]);
		$this->assertSame('articles/article', $data['items'][2]['id']);
	}
}
