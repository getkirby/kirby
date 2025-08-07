<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageTreeRequestController::class)]
class PageTreeRequestControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Request.PageTreeRequestController';

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
						'content'  => [
							'title' => 'Blog articles',
							'uuid'  => 'articles'
						],
						'children' => [
							[
								'slug'     => 'article-1',
								'template' => 'note',
								'content'  => [
									'uuid'  => 'article-1'
								],
							],
							[
								'slug' => 'article-2',
								'content'  => [
									'uuid'  => 'article-2'
								],
								'children' => [
									['slug' => 'subarticle']
								]
							],
							[
								'slug'     => 'article-3',
								'content'  => [
									'uuid'  => 'article-3'
								],
							],
						]
					]
				]
			],
			'blueprints' => [
				'pages/note' => [
					'sections' => [
						'albums' => [
							'type' => 'pages',
						],
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

	public function testEntryWithSite(): void
	{
		$controller = new PageTreeRequestController();
		$entry      = $controller->entry($this->app->site());

		$this->assertSame('/site', $entry['children']);
		$this->assertFalse($entry['disabled']);
		$this->assertTrue($entry['hasChildren']);
		$this->assertSame('home', $entry['icon']);
		$this->assertSame('/', $entry['id']);
		$this->assertFalse($entry['open']);
		$this->assertSame('Site', $entry['label']);
		$this->assertSame('/', $entry['url']);
		$this->assertSame('site://', $entry['uuid']);
		$this->assertSame('site://', $entry['value']);
	}

	public function testEntryWithPage(): void
	{
		$controller = new PageTreeRequestController();
		$entry      = $controller->entry($this->app->page('articles'));

		$this->assertSame('/pages/articles', $entry['children']);
		$this->assertFalse($entry['disabled']);
		$this->assertTrue($entry['hasChildren']);
		$this->assertSame('page', $entry['icon']);
		$this->assertSame('articles', $entry['id']);
		$this->assertFalse($entry['open']);
		$this->assertSame('Blog articles', $entry['label']);
		$this->assertSame('/articles', $entry['url']);
		$this->assertSame('page://articles', $entry['uuid']);
		$this->assertSame('page://articles', $entry['value']);
	}

	public function testEntryWithMoving(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'move' => '/pages/articles+article-2+subarticle',
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageTreeRequestController();
		$entry      = $controller->entry($this->app->page('articles/article-1'));
		$this->assertFalse($entry['disabled']);

		$entry = $controller->entry($this->app->page('articles/article-3'));
		$this->assertTrue($entry['disabled']);
	}

	public function testEntryWhenUuidsDisabled(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				]
			]
		]);

		$controller = new PageTreeRequestController();
		$entry      = $controller->entry($this->app->page('articles'));

		$this->assertSame('articles', $entry['id']);
		$this->assertNull($entry['uuid']);
		$this->assertSame('articles', $entry['value']);
	}

	public function testLoadForSite(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => null
				],
			]
		]);

		$controller = new PageTreeRequestController();
		$data       = $controller->load();

		$this->assertCount(1, $data);
		$this->assertSame('site://', $data[0]['value']);
	}

	public function testLoadForPage(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => '/pages/articles'
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageTreeRequestController();
		$data       = $controller->load();

		$this->assertCount(3, $data);
		$this->assertSame('page://article-1', $data[0]['value']);
		$this->assertSame('page://article-2', $data[1]['value']);
		$this->assertSame('page://article-3', $data[2]['value']);
		$this->assertFalse($data[0]['disabled']);
		$this->assertFalse($data[1]['disabled']);
		$this->assertFalse($data[2]['disabled']);
	}

	public function testLoadWithMove(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => '/pages/articles',
					'move'   => '/pages/articles+article-2',
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageTreeRequestController();
		$data       = $controller->load();

		$this->assertCount(3, $data);
		$this->assertSame('page://article-1', $data[0]['value']);
		$this->assertSame('page://article-2', $data[1]['value']);
		$this->assertSame('page://article-3', $data[2]['value']);
		$this->assertFalse($data[0]['disabled']);
		$this->assertTrue($data[1]['disabled']);
		$this->assertTrue($data[2]['disabled']);
	}
}
