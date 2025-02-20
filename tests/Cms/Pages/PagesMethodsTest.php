<?php

namespace Kirby\Cms;

class PagesMethodsTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'pagesMethods' => [
				'test' => fn () => 'pages method'
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
					]
				]
			]
		]);
	}

	public function testPagesMethod()
	{
		$pages = $this->app->site()->children();
		$this->assertSame('pages method', $pages->test());
	}
}
