<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class PageMethodsTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'pageMethods' => [
				'test' => fn () => 'page method'
			],
			'pagesMethods' => [
				'test' => fn () => 'pages method'
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							[
								'filename' => 'test.jpg'
							]
						]
					]
				]
			]
		]);
	}

	public function testPageMethod()
	{
		$page = $this->app->page('test');
		$this->assertSame('page method', $page->test());
	}

	public function testPagesMethod()
	{
		$pages = $this->app->site()->children();
		$this->assertSame('pages method', $pages->test());
	}
}
