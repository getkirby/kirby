<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\Site;

class SiteModelTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Api.SiteModel';

	public function testBlueprint(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'site' => [
					'title' => 'Test'
				]
			],
		]);

		$site      = $this->app->site();
		$blueprint = $this->attr($site, 'blueprint');

		$this->assertSame('Test', $blueprint['title']);
	}

	public function testChildren(): void
	{
		$this->app->impersonate('kirby');

		$site = new Site([
			'children' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$children = $this->attr($site, 'children');

		$this->assertSame('a', $children[0]['id']);
		$this->assertSame('b', $children[1]['id']);
	}

	public function testChildrenInaccessible(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'pages/children-restricted' => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'children' => [
					['slug' => 'a', 'num' => 1],
					['slug' => 'b', 'num' => 2, 'template' => 'children-restricted'],
					['slug' => 'c', 'num' => 3],
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$site  = $this->app->site();
		$model = $this->api->resolve($site)->select('children')->toArray();

		$childIds = array_column($model['children'], 'id');
		$this->assertContains('a', $childIds);
		$this->assertNotContains('b', $childIds);
		$this->assertContains('c', $childIds);
	}

	public function testContent(): void
	{
		$site = new Site([
			'content' => $content = [
				['a' => 'A'],
				['b' => 'B'],
			]
		]);

		$this->assertAttr($site, 'content', $content);
	}

	public function testDrafts(): void
	{
		$this->app->impersonate('kirby');

		$site = new Site([
			'drafts' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$drafts = $this->attr($site, 'drafts');

		$this->assertSame('a', $drafts[0]['id']);
		$this->assertSame('b', $drafts[1]['id']);
	}

	public function testDraftsInaccessible(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'pages/drafts-restricted' => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'drafts' => [
					['slug' => 'a'],
					['slug' => 'b', 'template' => 'drafts-restricted'],
					['slug' => 'c'],
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$site  = $this->app->site();
		$model = $this->api->resolve($site)->select('drafts')->toArray();

		$draftIds = array_column($model['drafts'], 'id');
		$this->assertContains('a', $draftIds);
		$this->assertNotContains('b', $draftIds);
		$this->assertContains('c', $draftIds);
	}

	public function testFiles(): void
	{
		$this->app->impersonate('kirby');

		$site = new Site([
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
			]
		]);

		$files = $this->attr($site, 'files');

		$this->assertSame('a.jpg', $files[0]['filename']);
		$this->assertSame('b.jpg', $files[1]['filename']);
	}

	public function testFilesInaccessible(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'files/files-restricted' => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'files' => [
					['filename' => 'a.jpg'],
					['filename' => 'b.jpg', 'template' => 'files-restricted'],
					['filename' => 'c.jpg'],
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$site  = $this->app->site();
		$model = $this->api->resolve($site)->select('files')->toArray();

		$filenames = array_column($model['files'], 'filename');
		$this->assertContains('a.jpg', $filenames);
		$this->assertNotContains('b.jpg', $filenames);
		$this->assertContains('c.jpg', $filenames);
	}

	public function testTitle(): void
	{
		$site = new Site([
			'content' => [
				'title' => 'Test',
			]
		]);

		$this->assertAttr($site, 'title', 'Test');
	}

	public function testUrl(): void
	{
		$site = new Site([
			'url' => 'https://getkirby.com'
		]);

		$this->assertAttr($site, 'url', 'https://getkirby.com');
	}
}
