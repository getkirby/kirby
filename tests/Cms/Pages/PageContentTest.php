<?php

namespace Kirby\Cms;

use TypeError;

class PageContentTest extends TestCase
{
	public function testDefaultContent()
	{
		$page = new Page(['slug' =>  'test']);
		$this->assertInstanceOf(Content::class, $page->content());
	}

	public function testContent()
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => $content = ['text' => 'lorem ipsum']
		]);

		$this->assertSame($content, $page->content()->toArray());
		$this->assertSame('lorem ipsum', $page->text()->value());
	}

	public function testInvalidContent()
	{
		$this->expectException(TypeError::class);
		new Page([
			'slug'    => 'test',
			'content' => 'content'
		]);
	}

	public function testEmptyTitle()
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => []
		]);

		$this->assertSame($page->slug(), $page->title()->value());
	}

	public function testTitle()
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Custom Title'
			]
		]);

		$this->assertSame('Custom Title', $page->title()->value());
	}
}
