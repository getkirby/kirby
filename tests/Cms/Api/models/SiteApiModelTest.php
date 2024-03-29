<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class SiteApiModelTest extends ApiModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteApiModel';

	public function testBlueprint()
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

	public function testChildren()
	{
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

	public function testContent()
	{
		$site = new Site([
			'content' => $content = [
				['a' => 'A'],
				['b' => 'B'],
			]
		]);

		$this->assertAttr($site, 'content', $content);
	}

	public function testDrafts()
	{
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

	public function testFiles()
	{
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

	public function testTitle()
	{
		$site = new Site([
			'content' => [
				'title' => 'Test',
			]
		]);

		$this->assertAttr($site, 'title', 'Test');
	}

	public function testUrl()
	{
		$site = new Site([
			'url' => 'https://getkirby.com'
		]);

		$this->assertAttr($site, 'url', 'https://getkirby.com');
	}
}
