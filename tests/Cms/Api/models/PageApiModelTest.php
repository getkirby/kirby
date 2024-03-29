<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class PageApiModelTest extends ApiModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageApiModel';

	public function testChildren()
	{
		$page = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$model = $this->api->resolve($page)->select('children')->toArray();

		$this->assertSame('test/a', $model['children'][0]['id']);
		$this->assertSame('test/b', $model['children'][1]['id']);
	}

	public function testContent()
	{
		$page = new Page([
			'slug' => 'test',
			'content' => $content = [
				'a' => 'A',
				'b' => 'B',
			]
		]);

		$this->assertAttr($page, 'content', $content);
	}

	public function testDrafts()
	{
		$page = new Page([
			'slug' => 'test',
			'drafts' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$model = $this->api->resolve($page)->select('drafts')->toArray();

		$this->assertSame('test/a', $model['drafts'][0]['id']);
		$this->assertSame('test/b', $model['drafts'][1]['id']);
	}

	public function testFiles()
	{
		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
			]
		]);

		$model = $this->api->resolve($page)->select('files')->toArray();

		$this->assertSame('a.jpg', $model['files'][0]['filename']);
		$this->assertSame('b.jpg', $model['files'][1]['filename']);
	}

	public function testHasDrafts()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertAttr($page, 'hasDrafts', false);

		$page = new Page([
			'slug' => 'test',
			'drafts' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$this->assertAttr($page, 'hasDrafts', true);
	}

	public function testHasChildren()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertAttr($page, 'hasChildren', false);

		$page = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$this->assertAttr($page, 'hasChildren', true);
	}

	public function testId()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'id', 'test');
	}

	public function testIsSortable()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'isSortable', $page->isSortable());
	}

	public function testNum()
	{
		$page = new Page([
			'slug' => 'test',
			'num'  => 2
		]);

		$this->assertAttr($page, 'num', 2);
	}

	public function testSlug()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'slug', 'test');
	}

	public function testStatus()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'status', 'unlisted');
	}

	public function testTemplate()
	{
		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$this->assertAttr($page, 'template', 'test');
	}

	public function testTitle()
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Test'
			]
		]);

		$this->assertAttr($page, 'title', 'Test');
	}

	public function testUrl()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'url', '/test');
	}
}
