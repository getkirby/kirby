<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageTreeController::class)]
class PageTreeControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.PageTreeController';
	public PageTreeController $tree;

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
		$this->tree = new PageTreeController($this->app->site());
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
		App::destroy();
	}

	public function testChildrenForSite(): void
	{
		$children = $this->tree->children(null);

		$this->assertCount(1, $children);
		$this->assertSame('site://', $children[0]['value']);
	}

	public function testChildrenForPage(): void
	{
		$children = $this->tree->children('/pages/articles');

		$this->assertCount(3, $children);
		$this->assertSame('page://article-1', $children[0]['value']);
		$this->assertSame('page://article-2', $children[1]['value']);
		$this->assertSame('page://article-3', $children[2]['value']);
		$this->assertFalse($children[0]['disabled']);
		$this->assertFalse($children[1]['disabled']);
		$this->assertFalse($children[2]['disabled']);
	}

	public function testChildrenWithMoving(): void
	{
		$children = $this->tree->children(
			'/pages/articles',
			'/pages/articles+article-2',
		);

		$this->assertCount(3, $children);
		$this->assertSame('page://article-1', $children[0]['value']);
		$this->assertSame('page://article-2', $children[1]['value']);
		$this->assertSame('page://article-3', $children[2]['value']);
		$this->assertFalse($children[0]['disabled']);
		$this->assertTrue($children[1]['disabled']);
		$this->assertTrue($children[2]['disabled']);
	}

	public function testEntryWithSite(): void
	{
		$entry = $this->tree->entry($this->app->site());

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
		$entry = $this->tree->entry($this->app->page('articles'));

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
		$entry = $this->tree->entry(
			$this->app->page('articles/article-1'),
			$this->app->page('articles/article-2/subarticle')
		);

		$this->assertFalse($entry['disabled']);

		$entry = $this->tree->entry(
			$this->app->page('articles/article-3'),
			$this->app->page('articles/article-2/subarticle')
		);

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

		$entry = $this->tree->entry($this->app->page('articles'));

		$this->assertSame('articles', $entry['id']);
		$this->assertNull($entry['uuid']);
		$this->assertSame('articles', $entry['value']);
	}

	public function testParents(): void
	{
		$parents = $this->tree->parents(
			page: 'articles/article-2/subarticle'
		);

		$this->assertSame([
			'page://articles',
			'page://article-2'
		], $parents['data']);

		$parents = $this->tree->parents(
			page: 'articles/article-2/subarticle',
			includeSite: true
		);

		$this->assertSame([
			'site://',
			'page://articles',
			'page://article-2'
		], $parents['data']);
	}

	public function testParentsWhenUuidsDisabled(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				]
			]
		]);

		$parents = $this->tree->parents(
			page: 'articles/article-2/subarticle'
		);

		$this->assertSame([
			'articles',
			'articles/article-2'
		], $parents['data']);

		$parents = $this->tree->parents(
			page: 'articles/article-2/subarticle',
			includeSite: true
		);

		$this->assertSame([
			'/',
			'articles',
			'articles/article-2'
		], $parents['data']);
	}
}
