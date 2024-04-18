<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class SiteMethodsTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'siteMethods' => [
				'test' => function () {
					return 'site method';
				}
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

	public function testSiteMethod()
	{
		$site = $this->app->site();
		$this->assertSame('site method', $site->test());
	}
}
